#!/bin/bash

# ============================================
#     Nexus - Cloudflared Tunnel Launcher
# ============================================

# Redirect stderr to suppress "Killed" messages
exec 2>/dev/null

# Get the directory where this script is located
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

# Colors
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[0;33m'
BOLD='\033[1m'
NC='\033[0m' # No Color

# Create logs directory
mkdir -p storage/logs
touch storage/logs/realtime-requests.log

# Store original .env values
if [ -f .env ]; then
    ORIGINAL_APP_URL=$(grep '^APP_URL=' .env | cut -d'=' -f2-)
    ORIGINAL_GOOGLE_REDIRECT=$(grep '^GOOGLE_REDIRECT_URI=' .env | cut -d'=' -f2-)
fi

# Kill existing processes
pkill -f "php artisan serve" 2>/dev/null
pkill -f "cloudflared tunnel" 2>/dev/null
pkill -9 -f "tail.*realtime-requests" 2>/dev/null
sleep 1

# Clear all caches
php artisan config:clear >/dev/null 2>&1
php artisan cache:clear >/dev/null 2>&1
php artisan view:clear >/dev/null 2>&1
php artisan route:clear >/dev/null 2>&1

# Function to cleanup on exit
CLEANUP_DONE=0
cleanup() {
    [ "$CLEANUP_DONE" -eq 1 ] && return
    CLEANUP_DONE=1
    
    echo ""
    echo -ne "  ${RED}Stopping services${NC}"
    pkill -9 -f "php artisan serve" 2>/dev/null
    pkill -9 -f "cloudflared tunnel" 2>/dev/null
    pkill -9 -f "tail.*realtime-requests" 2>/dev/null
    [ -n "$BG_PID" ] && kill -9 $BG_PID 2>/dev/null
    wait 2>/dev/null
    echo -e "${RED} ✓${NC}"

    echo -ne "  ${RED}Restoring configuration${NC}"
    sed -i 's|^APP_URL=.*|APP_URL=http://localhost:8000|' .env
    sed -i 's|^GOOGLE_REDIRECT_URI=.*|GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback|' .env

    php artisan config:clear >/dev/null 2>&1
    php artisan cache:clear >/dev/null 2>&1
    php artisan view:clear >/dev/null 2>&1
    php artisan route:clear >/dev/null 2>&1
    echo -e "${RED} ✓${NC}"

    echo ""
    echo -e "${RED}Tunnel stopped${NC}"
    echo ""
    echo "Configuration restored to localhost:"
    echo "  APP_URL: http://localhost:8000"
    echo "  GOOGLE_REDIRECT_URI: http://localhost:8000/auth/google/callback"
    echo ""
    echo -e "${RED}Done.${NC}"
    echo ""
    exit 0
}

trap cleanup SIGINT SIGTERM EXIT
echo ""
echo ""
echo -e "${GREEN}${BOLD}Cloudflared Tunnel Launcher${NC}"
echo ""

# Step 1: Start server
echo -ne "  ${GREEN}Starting server${NC}"
php artisan serve --host=0.0.0.0 --port=8000 >/dev/null 2>&1 &
for i in {1..10}; do
    HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" http://localhost:8000 2>/dev/null)
    if [ "$HTTP_CODE" = "200" ] || [ "$HTTP_CODE" = "302" ] || [ "$HTTP_CODE" = "500" ]; then
        echo -e "${GREEN} ✓${NC}"
        break
    fi
    echo -ne "${GREEN}.${NC}"
    sleep 1
done

# Step 2: Start Cloudflared tunnel
echo -ne "  ${GREEN}Starting Cloudflared tunnel${NC}"
TUNNEL_LOG=$(mktemp)
cloudflared tunnel --url http://localhost:8000 > "$TUNNEL_LOG" 2>&1 &

# Wait for tunnel URL with dots
for i in {1..30}; do
    sleep 1
    TUNNEL_URL=$(grep -oE 'https://[a-zA-Z0-9.-]+\.trycloudflare\.com|https://[a-zA-Z0-9.-]+\.lcl\.cloudflare\.com' "$TUNNEL_LOG" | head -1)
    if [ -n "$TUNNEL_URL" ]; then
        echo -e "${GREEN} ✓${NC}"
        break
    fi
    echo -ne "${GREEN}.${NC}"
done

if [ -z "$TUNNEL_URL" ]; then
    echo -e "${YELLOW}failed${NC}"
    rm -f "$TUNNEL_LOG"
    cleanup
    exit 1
fi

# Step 3: Update .env
echo -ne "  ${GREEN}Configuring .env${NC}"
sed -i "s|^APP_URL=.*|APP_URL=$TUNNEL_URL|" .env
sed -i "s|^GOOGLE_REDIRECT_URI=.*|GOOGLE_REDIRECT_URI=$TUNNEL_URL/auth/google/callback|" .env
php artisan config:clear >/dev/null 2>&1
php artisan cache:clear >/dev/null 2>&1
echo -e "${GREEN} ✓${NC}"

rm -f "$TUNNEL_LOG"

# Clear log and start visitor monitor
> storage/logs/realtime-requests.log

echo ""
echo -e "${GREEN}${BOLD}Tunnel is running!${NC}"
echo ""
echo -e "  Public URL:     $TUNNEL_URL"
echo -e "  OAuth Callback: $TUNNEL_URL/auth/google/callback"
echo ""
echo -e "  Press ${YELLOW}Ctrl+C${NC} to stop and restore localhost."
echo ""
echo -e "${GREEN}Visitor Logs:${NC}"
echo ""

# Start visitor monitor
(
    tail -n 5 -F storage/logs/realtime-requests.log 2>/dev/null | while IFS= read -r line; do
        [ -n "$line" ] || continue
        
        ts=$(echo "$line" | jq -r '.timestamp // ""' 2>/dev/null)
        ip=$(echo "$line" | jq -r '.ip // ""' 2>/dev/null)
        method=$(echo "$line" | jq -r '.method // ""' 2>/dev/null)
        path=$(echo "$line" | jq -r '.path // ""' 2>/dev/null)
        city=$(echo "$line" | jq -r '.city // ""' 2>/dev/null)
        country=$(echo "$line" | jq -r '.country // ""' 2>/dev/null)
        lat=$(echo "$line" | jq -r '.latitude // ""' 2>/dev/null)
        lon=$(echo "$line" | jq -r '.longitude // ""' 2>/dev/null)
        device=$(echo "$line" | jq -r '.device // ""' 2>/dev/null)
        browser=$(echo "$line" | jq -r '.browser // ""' 2>/dev/null)
        cfcountry=$(echo "$line" | jq -r '.cf_ip_country // ""' 2>/dev/null)
        
        case "$method" in
            GET) mc="\033[0;32m" ;;
            POST) mc="\033[0;33m" ;;
            PUT) mc="\033[0;34m" ;;
            DELETE) mc="\033[0;31m" ;;
            *) mc="\033[0;37m" ;;
        esac
        
        loc="$city, $country"
        [ "$city" = "Unknown" ] || [ -z "$city" ] && loc="$country"
        [ -n "$lat" ] && [ "$lat" != "null" ] && loc="$loc ($lat, $lon)"
        
        echo -e "[$ts]  $ip - ${mc}$method${NC} - $path"
        echo -e "   $loc"
        echo -e "   $device | $browser"
        [ -n "$cfcountry" ] && [ "$cfcountry" != "null" ] && echo -e "   Country: $cfcountry"
        echo -e "${GREEN}---------------------------------------${NC}"
        echo ""
    done
) &
BG_PID=$!

# Keep running
wait
