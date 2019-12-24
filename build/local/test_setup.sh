#!/bin/bash
#
# install magento to run tests on a local development box
#
# usage: build/local/test_setup.sh
#

set -euo pipefail
IFS=$'\n\t'

# whether or not magento is already installed, normally a quick-check based on file existence.
magento_is_installed() {
    local directory="${test_setup_directory}"
    local magento_local_env="${directory}/app/etc/env.php"

    if [ -e  "${magento_local_env}" ]; then
        return 0
    else
        return 1
    fi
}

ensure_magento2_auth()
{
    local directory="${test_setup_directory}"
    (
        cd "${directory}"
        php -f vendor/bin/composer -- config http-basic.repo.magento.com "${MAGENTO_CONNECT_USERNAME}" "${MAGENTO_CONNECT_PASSWORD}"
    )
}

source ./build/sh/magento_connect.sh

test_setup_basename="n98-magerun2"
test_setup_magerun_cmd="bin/${test_setup_basename}"
test_setup_directory="./magento2/www"
test_setup_db_host="127.0.0.1"
test_setup_db_port="${test_setup_db_port:-3306}"
test_setup_db_user="root"
test_setup_db_pass=""
test_setup_db_name="magento_magerun2_test"

if [ "" != "$(installed_version)" ]; then
    buildecho "version '$(installed_version)' already installed, skipping setup"
else
    ensure_environment
    ensure_mysql_db
    ensure_magento "magento-ce-2.1.5"
    ensure_magento2_auth
fi

# create stopfile if it does not yet exists
test_stopfile=".${test_setup_basename}"
if [ ! -f "${test_stopfile}" ]; then
    echo "${test_setup_directory}" > "${test_stopfile}"
    buildecho "stopfile ${test_stopfile} created: $(cat "${test_stopfile}")"
else
    buildecho "stopfile ${test_stopfile} exists: $(cat "${test_stopfile}")"
fi
