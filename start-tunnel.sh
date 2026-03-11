#!/bin/bash

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[0;33m'
BOLD='\033[1m'
NC='\033[0m'

mkdir -p storage/logs
touch storage/logs/realtime-requests.log

ORIGINAL_APP_URL="http://localhost:8000"
ORIGINAL_GOOGLE_REDIRECT="http://localhost:8000/auth/google/callback"

if [ -f .env ]; then
    ENV_APP_URL=$(grep '^APP_URL=' .env 2>/dev/null | head -1 | cut -d'=' -f2-)
    ENV_REDIRECT=$(grep '^GOOGLE_REDIRECT_URI=' .env 2>/dev/null | head -1 | cut -d'=' -f2-)

    if [ -n "$ENV_APP_URL" ] && [[ ! "$ENV_APP_URL" =~ "trycloudflare" ]] && [[ ! "$ENV_APP_URL" =~ "lcl.cloudflare" ]]; then
        ORIGINAL_APP_URL="$ENV_APP_URL"
    fi
    if [ -n "$ENV_REDIRECT" ] && [[ ! "$ENV_REDIRECT" =~ "trycloudflare" ]] && [[ ! "$ENV_REDIRECT" =~ "lcl.cloudflare" ]]; then
        ORIGINAL_GOOGLE_REDIRECT="$ENV_REDIRECT"
    fi
fi

echo -e "${BOLD}Cloudflared Tunnel${NC}"
echo ""

# Function to cleanup on exit
CLEANUP_DONE=0
cleanup() {
    [ "$CLEANUP_DONE" -eq 1 ] && return
    CLEANUP_DONE=1

    echo ""
    echo -e "${RED}Stopping tunnel...${NC}"
    
    pkill -9 -f "php artisan serve" 2>/dev/null
    pkill -9 -f "cloudflared tunnel" 2>/dev/null
    pkill -9 -f "tail.*realtime-requests" 2>/dev/null
    [ -n "$BG_PID" ] && kill -9 $BG_PID 2>/dev/null
    [ -n "$ANIM_PID" ] && kill -9 $ANIM_PID 2>/dev/null
    
    sleep 1
    
    if [ -f .env ]; then
        sed -i "s|^APP_URL=.*|APP_URL=$ORIGINAL_APP_URL|" .env
        sed -i "s|^GOOGLE_REDIRECT_URI=.*|GOOGLE_REDIRECT_URI=$ORIGINAL_GOOGLE_REDIRECT|" .env
        php artisan config:clear >/dev/null 2>&1
        php artisan cache:clear >/dev/null 2>&1
    fi
    
    echo -e "${GREEN}✓ Tunnel stopped${NC}"
    echo "Restored: APP_URL=$ORIGINAL_APP_URL"
    echo ""
    trap - EXIT SIGINT SIGTERM
    exit 0
}

trap cleanup SIGINT SIGTERM EXIT

animate_start() {
    local msg="$1"
    local spin=('⠋' '⠙' '⠹' '⠸' '⠼' '⠴' '⠦' '⠧' '⠇' '⠏')
    local i=0
    while true; do
        echo -ne "\r${spin[$i]} $msg"
        i=$(( (i + 1) % ${#spin[@]} ))
        sleep 0.08
    done
}

animate_done() {
    echo -ne "\r${GREEN}✓${NC} ${1}${GREEN} done${NC}   "
}

animate_fail() {
    echo -ne "\r${RED}✗${NC} ${1}${RED} failed${NC}   "
}

echo -ne "Starting Laravel server... "
msg="Starting Laravel server"
animate_start "$msg" &
ANIM_PID=$!
disown $ANIM_PID
nohup php -d display_errors=0 -d log_errors=0 -d error_reporting=0 artisan serve --host=0.0.0.0 --port=8000 > storage/logs/tunnel-server.log 2>&1 &
SERVER_PID=$!
disown $SERVER_PID

for i in {1..10}; do
    HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" http://localhost:8000 2>/dev/null)
    if [ "$HTTP_CODE" = "200" ] || [ "$HTTP_CODE" = "302" ] || [ "$HTTP_CODE" = "500" ]; then
        kill $ANIM_PID 2>/dev/null
        wait $ANIM_PID 2>/dev/null
        animate_done "$msg"
        echo ""
        break
    fi
    sleep 1
done

if ! kill -0 $SERVER_PID 2>/dev/null; then
    kill $ANIM_PID 2>/dev/null
    wait $ANIM_PID 2>/dev/null
    animate_fail "$msg"
    echo ""
    cleanup
    exit 1
fi

echo -ne "Starting Cloudflared tunnel... "
msg="Starting Cloudflared tunnel"
animate_start "$msg" &
ANIM_PID=$!
disown $ANIM_PID
TUNNEL_LOG=$(mktemp)
nohup cloudflared tunnel --url http://localhost:8000 > "$TUNNEL_LOG" 2>&1 &
TUNNEL_PID=$!
disown $TUNNEL_PID

TUNNEL_URL=""
for i in {1..30}; do
    sleep 1
    if [ -f "$TUNNEL_LOG" ]; then
        TUNNEL_URL=$(grep -oE 'https://[a-zA-Z0-9.-]+\.trycloudflare\.com|https://[a-zA-Z0-9.-]+\.lcl\.cloudflare\.com' "$TUNNEL_LOG" 2>/dev/null | head -1)
    fi
    if [ -n "$TUNNEL_URL" ]; then
        kill $ANIM_PID 2>/dev/null
        wait $ANIM_PID 2>/dev/null
        animate_done "$msg"
        echo ""
        break
    fi
done

if [ -z "$TUNNEL_URL" ]; then
    kill $ANIM_PID 2>/dev/null
    wait $ANIM_PID 2>/dev/null
    animate_fail "$msg"
    echo ""
    rm -f "$TUNNEL_LOG"
    echo -e "${RED}Error: Failed to start tunnel${NC}"
    echo -e "${YELLOW}Install cloudflared:${NC}"
    echo "  curl -L --output cloudflared.deb https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-linux-amd64.deb"
    echo "  sudo dpkg -i cloudflared.deb"
    cleanup
    exit 1
fi

rm -f "$TUNNEL_LOG"

echo -ne "Updating .env... "
msg="Updating .env"
animate_start "$msg" &
ANIM_PID=$!
if [ -f .env ]; then
    sed -i "s|^APP_URL=.*|APP_URL=$TUNNEL_URL|" .env
    sed -i "s|^GOOGLE_REDIRECT_URI=.*|GOOGLE_REDIRECT_URI=$TUNNEL_URL/auth/google/callback|" .env
    php artisan config:clear >/dev/null 2>&1
    php artisan cache:clear >/dev/null 2>&1
fi
kill $ANIM_PID 2>/dev/null
wait $ANIM_PID 2>/dev/null
animate_done "$msg"
echo ""

> storage/logs/realtime-requests.log

echo ""
echo -e "${BOLD}Tunnel running:${NC}"
echo "  URL: $TUNNEL_URL"
echo "  OAuth: $TUNNEL_URL/auth/google/callback"
echo ""
echo "Press Ctrl+C to stop"
echo ""

if command -v jq &> /dev/null; then
    echo -e "${BOLD}Visitor Logs:${NC}"
    echo ""

    (
        REQUEST_COUNT=0
        tail -n 5 -F storage/logs/realtime-requests.log 2>/dev/null | while IFS= read -r line; do
            [ -n "$line" ] || continue

            ip=$(echo "$line" | jq -r '.ip // "Unknown"' 2>/dev/null)
            method=$(echo "$line" | jq -r '.method // "Unknown"' 2>/dev/null)
            path=$(echo "$line" | jq -r '.path // "/"' 2>/dev/null)
            city=$(echo "$line" | jq -r '.city // "Unknown"' 2>/dev/null)
            country=$(echo "$line" | jq -r '.country // "Unknown"' 2>/dev/null)
            lat=$(echo "$line" | jq -r '.latitude // null' 2>/dev/null)
            lon=$(echo "$line" | jq -r '.longitude // null' 2>/dev/null)
            device=$(echo "$line" | jq -r '.device // "Unknown"' 2>/dev/null)
            browser=$(echo "$line" | jq -r '.browser // "Unknown"' 2>/dev/null)
            cfcountry=$(echo "$line" | jq -r '.cf_ip_country // ""' 2>/dev/null)
            username=$(echo "$line" | jq -r '.username // ""' 2>/dev/null)
            useremail=$(echo "$line" | jq -r '.user_email // ""' 2>/dev/null)

            [ "$ip" = "Unknown" ] || [ "$ip" = "null" ] || [ -z "$ip" ] && continue

            REQUEST_COUNT=$((REQUEST_COUNT + 1))

            case "$method" in
                GET) mc="\033[0;32m" ;;
                POST) mc="\033[0;33m" ;;
                PUT) mc="\033[0;34m" ;;
                DELETE) mc="\033[0;31m" ;;
                *) mc="\033[0;37m" ;;
            esac

            loc="$city, $country"
            [ "$city" = "Unknown" ] || [ -z "$city" ] && loc="$country"
            [ "$lat" != "null" ] && [ -n "$lat" ] && loc="$loc ($lat, $lon)"

            echo -e "${BOLD}${REQUEST_COUNT}${NC} - ${BOLD}$ip${NC}"
            [ -n "$username" ] && [ "$username" != "null" ] && [ -n "$useremail" ] && [ "$useremail" != "null" ] && echo -e "   ${BOLD}$username${NC} - $useremail"
            [ -n "$username" ] && [ "$username" != "null" ] && [ -z "$useremail" ] && echo -e "   ${BOLD}$username${NC}"
            echo -e "   ${mc}$method${NC} - $path"
            echo -e "   $loc"
            echo -e "   $device | $browser"
            [ -n "$cfcountry" ] && [ "$cfcountry" != "null" ] && [ "$cfcountry" != "" ] && echo -e "   Country: $cfcountry"
            echo -e "${GREEN}---------------------------------------${NC}"
            echo ""
        done
    ) &
    BG_PID=$!
    disown $BG_PID
else
    echo -e "${YELLOW}jq not installed - visitor logs disabled${NC}"
    echo "  Install with: sudo apt install jq"
    echo ""
fi

# Keep running - wait for interrupt
while true; do
    sleep 1
done
