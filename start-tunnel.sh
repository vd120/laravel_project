#!/bin/bash

# ============================================
# Laravel Social - Cloudflared Tunnel Launcher
# ============================================

cd /home/user/laravel_project

echo "============================================"
echo "   Laravel Social - Cloudflared Tunnel     "
echo "============================================"
echo ""

# Check if cloudflared is installed
if ! command -v cloudflared &> /dev/null; then
    echo "Error: cloudflared is not installed."
    echo ""
    echo "Install with:"
    echo "  curl -L https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-linux-amd64 -o cloudflared && sudo mv cloudflared /usr/local/bin/ && sudo chmod +x /usr/local/bin/cloudflared"
    exit 1
fi

# Kill existing processes
pkill -f "php artisan serve" 2>/dev/null
pkill -f "cloudflared tunnel" 2>/dev/null
sleep 1

# Clear config cache
php artisan config:clear 2>/dev/null

# Function to cleanup on exit
cleanup() {
    echo ""
    echo "Shutting down..."
    pkill -f "php artisan serve" 2>/dev/null
    pkill -f "cloudflared tunnel" 2>/dev/null
    
    # Reset .env to localhost
    sed -i 's|^APP_URL=.*|APP_URL=http://localhost|' .env
    sed -i 's|^APP_URL_EXTERNAL=.*|APP_URL_EXTERNAL=http://localhost|' .env
    sed -i 's|^GOOGLE_REDIRECT_URI=.*|GOOGLE_REDIRECT_URI=http://localhost/auth/google/callback|' .env
    php artisan config:clear 2>/dev/null
    
    echo "Done. Goodbye!"
    exit 0
}

trap cleanup SIGINT SIGTERM EXIT

# Start Laravel server
echo "Starting Laravel server..."
php artisan serve --host=0.0.0.0 --port=8000 &
sleep 2

# Start cloudflared and capture URL
echo "Starting Cloudflared tunnel..."
echo "Waiting for tunnel URL..."
echo ""

TUNNEL_LOG=$(mktemp)
cloudflared tunnel --url http://localhost:8000 2>&1 | tee "$TUNNEL_LOG" &

# Wait for tunnel URL
TUNNEL_URL=""
for i in {1..30}; do
    sleep 1
    TUNNEL_URL=$(grep -o 'https://[a-zA-Z0-9-]*\.trycloudflare\.com' "$TUNNEL_LOG" | head -1)
    if [ -n "$TUNNEL_URL" ]; then
        break
    fi
done

if [ -z "$TUNNEL_URL" ]; then
    echo "Failed to get tunnel URL."
    exit 1
fi

# Update .env with tunnel URL
echo "Configuring .env with tunnel URL..."
sed -i "s|^APP_URL=.*|APP_URL=$TUNNEL_URL|" .env
sed -i "s|^APP_URL_EXTERNAL=.*|APP_URL_EXTERNAL=$TUNNEL_URL|" .env
sed -i "s|^GOOGLE_REDIRECT_URI=.*|GOOGLE_REDIRECT_URI=$TUNNEL_URL/auth/google/callback|" .env
php artisan config:clear 2>/dev/null

# Display success
echo ""
echo "============================================"
echo "   Tunnel is running!"
echo "============================================"
echo ""
echo "Public URL:     $TUNNEL_URL"
echo "OAuth Callback: $TUNNEL_URL/auth/google/callback"
echo ""
echo "Press Ctrl+C to stop."
echo ""

# Keep running
wait