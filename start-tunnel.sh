#!/bin/bash

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

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

echo ""
echo "Cloudflare Tunnel"
echo "─────────────────────────────────────────"
echo ""

# cleanup any existing processes first
echo "Cleaning up existing processes..."
pkill -9 -f "php artisan serve" 2>/dev/null
pkill -9 -f "php artisan queue:work" 2>/dev/null
pkill -9 -f "cloudflared tunnel" 2>/dev/null
sleep 1

# Create tunnel log file for health monitoring
TUNNEL_LOG=$(mktemp)

# Function to cleanup on exit
CLEANUP_DONE=0
cleanup() {
    [ "$CLEANUP_DONE" -eq 1 ] && return
    CLEANUP_DONE=1

    echo ""
    echo "Stopping tunnel..."

    pkill -9 -f "php artisan serve" 2>/dev/null
    pkill -9 -f "php artisan queue:work" 2>/dev/null
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

    rm -f "$TUNNEL_LOG"

    echo "Tunnel stopped"
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
    echo -ne "\r[OK] $1 done   "
}

animate_fail() {
    echo -ne "\r[FAIL] $1 failed   "
}

verify_tunnel_url() {
    local url="$1"
    local max_attempts=10
    local attempt=1
    
    while [ $attempt -le $max_attempts ]; do
        HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" --max-time 5 "$url" 2>/dev/null)
        if [ "$HTTP_CODE" = "200" ] || [ "$HTTP_CODE" = "302" ] || [ "$HTTP_CODE" = "500" ]; then
            return 0
        fi
        sleep 2
        attempt=$((attempt + 1))
    done
    return 1
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

echo -ne "Starting queue worker... "
msg="Starting queue worker"
animate_start "$msg" &
ANIM_PID=$!
disown $ANIM_PID
nohup php -d display_errors=0 -d log_errors=0 -d error_reporting=0 artisan queue:work --sleep=5 --tries=3 --max-jobs=1000 > storage/logs/queue-worker.log 2>&1 &
QUEUE_PID=$!
disown $QUEUE_PID

sleep 2

if kill -0 $QUEUE_PID 2>/dev/null; then
    kill $ANIM_PID 2>/dev/null
    wait $ANIM_PID 2>/dev/null
    animate_done "$msg"
    echo ""
else
    kill $ANIM_PID 2>/dev/null
    wait $ANIM_PID 2>/dev/null
    animate_fail "$msg"
    echo ""
    echo "Queue worker failed to start"
fi

echo -ne "Starting Cloudflared tunnel... "
msg="Starting Cloudflared tunnel"
animate_start "$msg" &
ANIM_PID=$!
disown $ANIM_PID
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
    echo "Failed to start tunnel"
    echo ""
    echo "Install cloudflared:"
    echo "  curl -L --output cloudflared.deb https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-linux-amd64.deb"
    echo "  sudo dpkg -i cloudflared.deb"
    echo ""
    cleanup
    exit 1
fi

echo -ne "Verifying tunnel connection... "
msg="Verifying tunnel connection"
animate_start "$msg" &
ANIM_PID=$!
disown $ANIM_PID

if verify_tunnel_url "$TUNNEL_URL"; then
    kill $ANIM_PID 2>/dev/null
    wait $ANIM_PID 2>/dev/null
    animate_done "$msg"
    echo ""
else
    kill $ANIM_PID 2>/dev/null
    wait $ANIM_PID 2>/dev/null
    animate_fail "$msg"
    echo ""
    echo "Tunnel may take a moment to propagate"
fi

echo -ne "Updating environment... "
msg="Updating environment"
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
echo "Tunnel is ready"
echo ""
echo "Tunnel URL"
echo "  $TUNNEL_URL"
echo ""
echo "OAuth Callback"
echo "  $TUNNEL_URL/auth/google/callback"
echo ""
echo "Press Ctrl+C to stop"
echo ""

if command -v jq &> /dev/null; then
    echo "Live Traffic"
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

            loc="$city, $country"
            [ "$city" = "Unknown" ] || [ -z "$city" ] && loc="$country"
            [ "$lat" != "null" ] && [ -n "$lat" ] && loc="$loc ($lat, $lon)"

            echo "#$REQUEST_COUNT - $ip"
            [ -n "$username" ] && [ "$username" != "null" ] && [ -n "$useremail" ] && [ "$useremail" != "null" ] && echo "   $username - $useremail"
            [ -n "$username" ] && [ "$username" != "null" ] && [ -z "$useremail" ] && echo "   $username"
            echo "   $method - $path"
            echo "   Location: $loc"
            echo "   $device • $browser"
            [ -n "$cfcountry" ] && [ "$cfcountry" != "null" ] && [ "$cfcountry" != "" ] && echo "   Country: $cfcountry"
            echo ""
        done
    ) &
    BG_PID=$!
    disown $BG_PID
else
    echo "Live traffic disabled (install jq to enable)"
    echo ""
fi

# Keep running - wait for interrupt with health monitoring
echo "Monitoring tunnel health..."
echo ""

LAST_CHECK=$(date +%s)
while true; do
    sleep 5

    # Check if cloudflared is still running
    if ! pgrep -f "cloudflared tunnel" > /dev/null 2>&1; then
        echo "Tunnel disconnected - reconnecting..."
        nohup cloudflared tunnel --url http://localhost:8000 > "$TUNNEL_LOG" 2>&1 &
        TUNNEL_PID=$!
        disown $TUNNEL_PID

        # Wait for reconnection
        for i in {1..15}; do
            sleep 1
            if [ -f "$TUNNEL_LOG" ]; then
                NEW_URL=$(grep -oE 'https://[a-zA-Z0-9.-]+\.trycloudflare\.com|https://[a-zA-Z0-9.-]+\.lcl\.cloudflare\.com' "$TUNNEL_LOG" 2>/dev/null | head -1)
                if [ -n "$NEW_URL" ]; then
                    TUNNEL_URL="$NEW_URL"
                    sed -i "s|^APP_URL=.*|APP_URL=$TUNNEL_URL|" .env
                    sed -i "s|^GOOGLE_REDIRECT_URI=.*|GOOGLE_REDIRECT_URI=$TUNNEL_URL/auth/google/callback|" .env
                    php artisan config:clear >/dev/null 2>&1
                    php artisan cache:clear >/dev/null 2>&1
                    echo "Reconnected: $TUNNEL_URL"
                    break
                fi
            fi
        done
    fi

    # Periodic health check every 60 seconds
    CURRENT_TIME=$(date +%s)
    if [ $((CURRENT_TIME - LAST_CHECK)) -ge 60 ]; then
        if ! curl -s -o /dev/null -w "%{http_code}" --max-time 5 "$TUNNEL_URL" 2>/dev/null | grep -qE "^(200|302|500)$"; then
            echo "Tunnel health check failed - may need restart"
        fi
        LAST_CHECK=$CURRENT_TIME
    fi
done
