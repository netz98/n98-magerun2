#!/bin/bash
#
# Build script on Travis to select what to do from ${BUILD}
#
# usage: build/travis/script.sh
#

set -euo pipefail
IFS=$'\n\t'

echo "running travis build script ..."

echo "running script job '${SCRIPT_JOB:=DEFAULT}' ..."

case "${SCRIPT_JOB}" in

    "DEFAULT" )
    # run phpunit in magento2 mode "default"
    echo "run phpunit in magento 2 mode:"
    php -f "./${MAGENTO_VERSION}/bin/magento" deploy:mode:show
    vendor/bin/phpunit --debug
    ;;

    "PHP-CS-FIXER" )
    vendor/bin/php-cs-fixer --diff --dry-run -v fix
    (
        cd shared
        ../vendor/bin/php-cs-fixer --diff --dry-run -v fix
    )
    ;;

    "BUILDSH" )
    build/travis/build.sh
    ;;

esac
