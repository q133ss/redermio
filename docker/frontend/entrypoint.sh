#!/bin/sh
set -eu

mkdir -p /app/public

cat > /app/public/runtime-config.js <<EOF
window.__REDERMIO_CONFIG__ = {
  apiUrl: "${FRONTEND_API_URL:-http://localhost:8080/api}"
};
EOF

if [ ! -d /app/node_modules ] || [ ! -f /app/node_modules/@angular/cli/package.json ]; then
  npm install --no-audit --no-fund
fi

exec ng serve --host 0.0.0.0 --port 4200 --poll 2000
