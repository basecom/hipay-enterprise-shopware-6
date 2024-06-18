#!/bin/bash

echo ""
echo "-> Running linting suite for all existing customer-specific plugins"
echo ""

for d in custom/static-plugins/*/ ; do

    echo ""
    echo "-> Running for ${d}"
    echo ""

    # Prepare
    (cd ${d} && composer install)
    (cd ${d} && npm ci)

    # Execute
    (cd ${d} && ./vendor/bin/php-cs-fixer fix)
    (cd ${d} && ./vendor/bin/phpstan analyse --memory-limit=1G)
    (cd ${d} && ./node_modules/.bin/prettier --write "src/**/*.{yaml,yml,json,md}")
done
