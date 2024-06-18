#!/bin/bash

set -euo pipefail

function cleanup() {
    rm -f /tmp/plugin-list.json
    rm -f /tmp/plugin-list-upgradable
}

php bin/console plugin:list --json > /tmp/plugin-list.json

# Write plugin names to file where upgradeVersion is not null (= plugin can be upgraded)
jq -r '.[] | select( .upgradeVersion != null) | .name' /tmp/plugin-list.json > /tmp/plugin-list-upgradable

if [ ! -s /tmp/plugin-list-upgradable ]; then
    echo "All plugins are up-to-date :)"
    cleanup
    exit;
fi

xargs -L1 php bin/console plugin:update < /tmp/plugin-list-upgradable

php bin/console cache:clear

cleanup
