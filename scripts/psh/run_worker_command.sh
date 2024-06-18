#!/bin/bash

set -e

cd "$PLATFORM_APP_DIR"

CACHE_TREE_ID="$(test ! -f var/cache_tree.id || cat var/cache_tree.id)"
# Check if a new version was deployed.
if [[ "$PLATFORM_TREE_ID" != "$CACHE_TREE_ID" ]]; then
    # We must clear the cache in the worker nodes seperatly because the cache folder is not shared with the web container.
    echo "Clearing cache because new release was detected..."
    # Hard delete all cache directories, otherwise the storage fills up with cache folders of older releases.
    rm -rf var/cache/prod_*
    ./bin/console cache:clear
    echo "$PLATFORM_TREE_ID" > var/cache_tree.id
fi

# Use verbose to see the processed messages in the app.log
./bin/console "${@}" --memory-limit="$(jq .info.limits.memory < /run/config.json)M" --time-limit=295 -vv

echo "Cleaning up logs..."

# Remove files older than 24 hours
find var/log/ -type f -name "*.log" -cmin +1440 -delete

# Remove log files that are larger than 40MB
find var/log/ -type f -name "*.log" -size +40M -delete
