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

    # run phpunit in magento2 mode "production"
    echo "memory_limit=-1" >> ~/.phpenv/versions/$(phpenv version-name)/etc/conf.d/travis.ini
    (
      cd "./${MAGENTO_VERSION}"
      rm -rf var/{cache,di,generation,view_preprocessed}
      php -f bin/magento -- deploy:mode:set --skip-compilation production
      php -f bin/magento -- setup:static-content:deploy
      php -f bin/magento -- setup:di:compile
      if ! php -f bin/magento -- deploy:mode:show | grep -q production; then
        >&2 echo "error: failed to switch to production mode"
        exit 1
      fi
    )
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
