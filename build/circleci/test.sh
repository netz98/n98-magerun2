#!/bin/bash
set -euo pipefail
IFS=$'\n\t'

. build/circleci/source.sh

buildecho "php version:"
php --version

export N98_MAGERUN2_TEST_MAGENTO_ROOT="./${MAGENTO_VERSION}"
buildecho "magento test root '${N98_MAGERUN2_TEST_MAGENTO_ROOT}' exported as \$N98_MAGERUN2_TEST_MAGENTO_ROOT."

buildecho "run magerun phpunit testsuite:"
php -f vendor/phpunit/phpunit/phpunit -- --coverage-clover "${CLOVER_XML}"  \
        --log-junit "${CIRCLE_TEST_REPORTS}/junit/junit.xml"

buildecho "check coverage percentage:"
php -f tests/check-coverage.php -- "${CLOVER_XML}" "${COVERAGE}"
