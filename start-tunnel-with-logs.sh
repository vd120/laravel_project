#!/bin/bash

# Nexus Tunnel with Live Log Viewer
# Starts tunnel + shows real-time traffic logs

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

mkdir -p storage/logs

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
echo "Cloudflare Tunnel with Live Logs"
echo "================================="
echo ""

# Cleanup existing processes
echo "Cleaning up existing processes..."
pkill -9 -f "php artisan serve" 2>/dev/null
pkill -9 -f "php artisan queue:work" 2>/dev/null
pkill -9 -f "cloudflared tunnel" 2>/dev/null
pkill -9 -f "tail.*laravel-" 2>/dev/null
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
    pkill -9 -f "tail.*laravel-" 2>/dev/null
    [ -n "$BG_PID" ] && kill -9 $BG_PID 2>/dev/null

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

# Start Laravel server
echo "Starting Laravel server..."
php artisan serve --host=0.0.0.0 --port=8000 > storage/logs/tunnel-server.log 2>&1 &
SERVER_PID=$!
sleep 2

if ! kill -0 $SERVER_PID 2>/dev/null; then
    echo "Failed to start Laravel server"
    cleanup
    exit 1
fi
echo "Laravel server started"

# Start Queue worker
echo "Starting queue worker..."
php artisan queue:work --sleep=5 --tries=3 --max-jobs=1000 > storage/logs/queue-worker.log 2>&1 &
QUEUE_PID=$!
sleep 2

if kill -0 $QUEUE_PID 2>/dev/null; then
    echo "Queue worker started"
else
    echo "Queue worker failed to start"
fi

# Start Cloudflared tunnel
echo "Starting Cloudflared tunnel..."
cloudflared tunnel --url http://localhost:8000 > "$TUNNEL_LOG" 2>&1 &
TUNNEL_PID=$!

TUNNEL_URL=""
for i in {1..30}; do
    sleep 1
    if [ -f "$TUNNEL_LOG" ]; then
        TUNNEL_URL=$(grep -oE 'https://[a-zA-Z0-9.-]+\.trycloudflare\.com' "$TUNNEL_LOG" 2>/dev/null | head -1)
    fi
    if [ -n "$TUNNEL_URL" ]; then
        break
    fi
done

if [ -z "$TUNNEL_URL" ]; then
    echo "Failed to start tunnel"
    rm -f "$TUNNEL_LOG"
    echo ""
    echo "Install cloudflared:"
    echo "  curl -L --output cloudflared.deb https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-linux-amd64.deb"
    echo "  sudo dpkg -i cloudflared.deb"
    echo ""
    cleanup
    exit 1
fi

# Update .env
if [ -f .env ]; then
    sed -i "s|^APP_URL=.*|APP_URL=$TUNNEL_URL|" .env
    sed -i "s|^GOOGLE_REDIRECT_URI=.*|GOOGLE_REDIRECT_URI=$TUNNEL_URL/auth/google/callback|" .env
    php artisan config:clear >/dev/null 2>&1
    php artisan cache:clear >/dev/null 2>&1
fi

echo ""
echo "================================="
echo "Tunnel is ready"
echo "================================="
echo ""
echo "Tunnel URL: $TUNNEL_URL"
echo ""
echo "OAuth Callback: $TUNNEL_URL/auth/google/callback"
echo ""
echo "Press Ctrl+C to stop"
echo ""
echo "Live Requests:"
echo "-------------------------------------------"

# Start live log viewer
(
    TODAY=$(date +%Y-%m-%d)
    DAILY_LOG="storage/logs/laravel-${TODAY}.log"
    
    # Wait for log file to be created
    while [ ! -f "$DAILY_LOG" ]; do
        sleep 1
    done
    
    tail -n 0 -F "$DAILY_LOG" 2>/dev/null | while IFS= read -r line; do
        # Only process "Request" log entries
        if [[ "$line" != *"Request"* ]]; then
            continue
        fi

        # Extract JSON from log line
        JSON=$(echo "$line" | grep -oE '\{.*\}' | head -1)
        
        if [ -n "$JSON" ]; then
            # Parse with Python
            echo "$JSON" | python3 -c "
import sys, json, re
try:
    data = json.loads(sys.stdin.read())
    ip = data.get('ip', '?')
    method = data.get('method', '?')
    path = data.get('path', '/')
    username = data.get('username', '')
    user_email = data.get('user_email', '')
    country = data.get('country', '?')
    device = data.get('device', '?')
    browser = data.get('browser', '?')
    cf_country = data.get('cf_ip_country', '')

    if ip == '?' or not ip:
        sys.exit(0)

    # Country name mapping (ISO 3166-1 alpha-2)
    countries = {
        'AF': 'Afghanistan', 'AX': 'Aland Islands', 'AL': 'Albania', 'DZ': 'Algeria',
        'AS': 'American Samoa', 'AD': 'Andorra', 'AO': 'Angola', 'AI': 'Anguilla',
        'AQ': 'Antarctica', 'AG': 'Antigua And Barbuda', 'AR': 'Argentina', 'AM': 'Armenia',
        'AW': 'Aruba', 'AU': 'Australia', 'AT': 'Austria', 'AZ': 'Azerbaijan',
        'BS': 'Bahamas', 'BH': 'Bahrain', 'BD': 'Bangladesh', 'BB': 'Barbados',
        'BY': 'Belarus', 'BE': 'Belgium', 'BZ': 'Belize', 'BJ': 'Benin',
        'BM': 'Bermuda', 'BT': 'Bhutan', 'BO': 'Bolivia', 'BA': 'Bosnia And Herzegovina',
        'BW': 'Botswana', 'BR': 'Brazil', 'IO': 'British Indian Ocean Territory',
        'BN': 'Brunei Darussalam', 'BG': 'Bulgaria', 'BF': 'Burkina Faso', 'BI': 'Burundi',
        'KH': 'Cambodia', 'CM': 'Cameroon', 'CA': 'Canada', 'CV': 'Cape Verde',
        'KY': 'Cayman Islands', 'CF': 'Central African Republic', 'TD': 'Chad',
        'CL': 'Chile', 'CN': 'China', 'CX': 'Christmas Island',
        'CC': 'Cocos (Keeling) Islands', 'CO': 'Colombia', 'KM': 'Comoros',
        'CG': 'Congo', 'CD': 'Congo, Democratic Republic', 'CK': 'Cook Islands',
        'CR': 'Costa Rica', 'CI': 'Cote D\'Ivoire', 'HR': 'Croatia', 'CU': 'Cuba',
        'CY': 'Cyprus', 'CZ': 'Czech Republic', 'DK': 'Denmark', 'DJ': 'Djibouti',
        'DM': 'Dominica', 'DO': 'Dominican Republic', 'EC': 'Ecuador', 'EG': 'Egypt',
        'SV': 'El Salvador', 'GQ': 'Equatorial Guinea', 'ER': 'Eritrea', 'EE': 'Estonia',
        'ET': 'Ethiopia', 'FK': 'Falkland Islands', 'FO': 'Faroe Islands', 'FJ': 'Fiji',
        'FI': 'Finland', 'FR': 'France', 'GF': 'French Guiana', 'PF': 'French Polynesia',
        'GA': 'Gabon', 'GM': 'Gambia', 'GE': 'Georgia', 'DE': 'Germany', 'GH': 'Ghana',
        'GI': 'Gibraltar', 'GR': 'Greece', 'GL': 'Greenland', 'GD': 'Grenada',
        'GP': 'Guadeloupe', 'GU': 'Guam', 'GT': 'Guatemala', 'GG': 'Guernsey',
        'GN': 'Guinea', 'GW': 'Guinea-Bissau', 'GY': 'Guyana', 'HT': 'Haiti',
        'VA': 'Holy See (Vatican City State)', 'HN': 'Honduras', 'HK': 'Hong Kong',
        'HU': 'Hungary', 'IS': 'Iceland', 'IN': 'India', 'ID': 'Indonesia',
        'IR': 'Iran', 'IQ': 'Iraq', 'IE': 'Ireland', 'IM': 'Isle Of Man',
        'IL': 'Israel', 'IT': 'Italy', 'JM': 'Jamaica', 'JP': 'Japan',
        'JE': 'Jersey', 'JO': 'Jordan', 'KZ': 'Kazakhstan', 'KE': 'Kenya',
        'KI': 'Kiribati', 'KR': 'Korea', 'KP': 'North Korea', 'KW': 'Kuwait',
        'KG': 'Kyrgyzstan', 'LA': 'Laos', 'LV': 'Latvia', 'LB': 'Lebanon',
        'LS': 'Lesotho', 'LR': 'Liberia', 'LY': 'Libya', 'LI': 'Liechtenstein',
        'LT': 'Lithuania', 'LU': 'Luxembourg', 'MO': 'Macao', 'MK': 'Macedonia',
        'MG': 'Madagascar', 'MW': 'Malawi', 'MY': 'Malaysia', 'MV': 'Maldives',
        'ML': 'Mali', 'MT': 'Malta', 'MH': 'Marshall Islands', 'MQ': 'Martinique',
        'MR': 'Mauritania', 'MU': 'Mauritius', 'YT': 'Mayotte', 'MX': 'Mexico',
        'FM': 'Micronesia', 'MD': 'Moldova', 'MC': 'Monaco', 'MN': 'Mongolia',
        'ME': 'Montenegro', 'MS': 'Montserrat', 'MA': 'Morocco', 'MZ': 'Mozambique',
        'MM': 'Myanmar', 'NA': 'Namibia', 'NR': 'Nauru', 'NP': 'Nepal',
        'NL': 'Netherlands', 'NC': 'New Caledonia', 'NZ': 'New Zealand', 'NI': 'Nicaragua',
        'NE': 'Niger', 'NG': 'Nigeria', 'NU': 'Niue', 'NF': 'Norfolk Island',
        'MP': 'Northern Mariana Islands', 'NO': 'Norway', 'OM': 'Oman', 'PK': 'Pakistan',
        'PW': 'Palau', 'PS': 'Palestinian Territory', 'PA': 'Panama', 'PG': 'Papua New Guinea',
        'PY': 'Paraguay', 'PE': 'Peru', 'PH': 'Philippines', 'PN': 'Pitcairn',
        'PL': 'Poland', 'PT': 'Portugal', 'PR': 'Puerto Rico', 'QA': 'Qatar',
        'RE': 'Reunion', 'RO': 'Romania', 'RU': 'Russian Federation', 'RW': 'Rwanda',
        'BL': 'Saint Barthelemy', 'SH': 'Saint Helena', 'KN': 'Saint Kitts And Nevis',
        'LC': 'Saint Lucia', 'MF': 'Saint Martin', 'PM': 'Saint Pierre And Miquelon',
        'VC': 'Saint Vincent And Grenadines', 'WS': 'Samoa', 'SM': 'San Marino',
        'ST': 'Sao Tome And Principe', 'SA': 'Saudi Arabia', 'SN': 'Senegal',
        'RS': 'Serbia', 'SC': 'Seychelles', 'SL': 'Sierra Leone', 'SG': 'Singapore',
        'SK': 'Slovakia', 'SI': 'Slovenia', 'SB': 'Solomon Islands', 'SO': 'Somalia',
        'ZA': 'South Africa', 'ES': 'Spain', 'LK': 'Sri Lanka', 'SD': 'Sudan',
        'SR': 'Suriname', 'SJ': 'Svalbard And Jan Mayen', 'SZ': 'Swaziland',
        'SE': 'Sweden', 'CH': 'Switzerland', 'SY': 'Syrian Arab Republic',
        'TW': 'Taiwan', 'TJ': 'Tajikistan', 'TZ': 'Tanzania', 'TH': 'Thailand',
        'TL': 'Timor-Leste', 'TG': 'Togo', 'TK': 'Tokelau', 'TO': 'Tonga',
        'TT': 'Trinidad And Tobago', 'TN': 'Tunisia', 'TR': 'Turkey', 'TM': 'Turkmenistan',
        'TC': 'Turks And Caicos Islands', 'TV': 'Tuvalu', 'UG': 'Uganda', 'UA': 'Ukraine',
        'AE': 'United Arab Emirates', 'GB': 'United Kingdom', 'US': 'United States',
        'UY': 'Uruguay', 'UZ': 'Uzbekistan', 'VU': 'Vanuatu', 'VE': 'Venezuela',
        'VN': 'Vietnam', 'VG': 'Virgin Islands, British', 'VI': 'Virgin Islands, U.S.',
        'WF': 'Wallis And Futuna', 'EH': 'Western Sahara', 'YE': 'Yemen',
        'ZM': 'Zambia', 'ZW': 'Zimbabwe',
        'T1': 'Tor/Anonymous',
    }

    # Use cf_country (from Cloudflare) for mapping
    display_country = countries.get(cf_country, cf_country) if cf_country else country
    if display_country == '?' or not display_country:
        display_country = cf_country if cf_country else country

    user_info = ''
    if username:
        user_info = ' (' + username
        if user_email:
            user_info += ' - ' + user_email
        user_info += ')'

    print(f'{ip}{user_info} - {method} {path} - {display_country} - {device} {browser}')
except Exception as e:
    pass
" 2>/dev/null
        fi
    done
) &
BG_PID=$!
disown $BG_PID

# Health monitoring
echo "Monitoring tunnel health..."
echo ""

LAST_CHECK=$(date +%s)
while true; do
    sleep 5

    # Check if cloudflared is still running
    if ! pgrep -f "cloudflared tunnel" > /dev/null 2>&1; then
        echo "Tunnel disconnected - reconnecting..."
        cloudflared tunnel --url http://localhost:8000 > "$TUNNEL_LOG" 2>&1 &
        TUNNEL_PID=$!
        disown $TUNNEL_PID

        # Wait for reconnection
        for i in {1..15}; do
            sleep 1
            if [ -f "$TUNNEL_LOG" ]; then
                NEW_URL=$(grep -oE 'https://[a-zA-Z0-9.-]+\.trycloudflare\.com' "$TUNNEL_LOG" 2>/dev/null | head -1)
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
