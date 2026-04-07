#!/usr/bin/env sh
set -eu

if [ "$#" -ne 1 ]; then
  echo "Usage: sh scripts/set-baseurl.sh <base-url>"
  echo "Example: sh scripts/set-baseurl.sh http://127.0.0.1:8086/"
  exit 1
fi

BASE_URL="$1"

case "$BASE_URL" in
  http://*/|https://*/)
    ;;
  *)
    echo "Error: base URL must start with http:// or https:// and end with /"
    echo "Example valid value: http://localhost:8080/"
    exit 1
    ;;
esac

ENV_FILE=".env"
if [ ! -f "$ENV_FILE" ]; then
  echo "Error: .env not found in current directory"
  exit 1
fi

if grep -Eq "^[[:space:]]*app\.baseURL[[:space:]]*=" "$ENV_FILE"; then
  sed -i.bak -E "s|^[[:space:]]*app\.baseURL[[:space:]]*=.*$|app.baseURL = '$BASE_URL'|" "$ENV_FILE"
else
  printf "\napp.baseURL = '%s'\n" "$BASE_URL" >> "$ENV_FILE"
  cp "$ENV_FILE" "$ENV_FILE.bak"
fi

echo "Updated app.baseURL to: $BASE_URL"
echo "Backup file: $ENV_FILE.bak"
