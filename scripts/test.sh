#!/bin/bash

echo ""
echo "-> Running test suite for all existing customer-specific plugins"
echo ""

for d in custom/static-plugins/*/ ; do

    echo ""
    echo "-> Running for ${d}"
    echo ""

    # Prepare
    (cd ${d} && composer install)

    # Execute
    if [ -z ${filter+x} ];
    then
        php -dpcov.enabled=1 -dpcov.directory="${d}/." ${d}/vendor/bin/phpunit -c "${d}/phpunit.xml.dist" --testdox --colors=always
    else
        php -dpcov.enabled=1 -dpcov.directory="${d}/." ${d}/vendor/bin/phpunit -c "${d}/phpunit.xml.dist" --testdox --colors=always --filter ${filter}
    fi
done
