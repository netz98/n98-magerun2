#!/bin/bash
set -euo pipefail
IFS=$'\n\t'

. build/circleci/source.sh

# download and install mangento (by the git cloned magerun version itself)
magerun_install()
{
    local version="${1}"
    local space="."
    local dir="${space}/${version}"
    local data="${2:-no}"


    # mysql -uroot -e 'CREATE DATABASE IF NOT EXISTS `magento_travis`;'

    php -dmemory_limit=1g -f bin/n98-magerun2 -- install \
            --magentoVersionByName="${version}" --installationFolder="${dir}" \
            --dbHost=127.0.0.1 --dbUser=root --dbPass="" --dbName="magento_travis" \
            --installSampleData=${data} --useDefaultConfigParams=yes \
            --baseUrl="http://travis.magento.local/"
}

# install mysql 5.6
sudo apt-add-repository -y 'deb http://ppa.launchpad.net/ondrej/mysql-experimental/ubuntu precise main'
sudo apt-get update; sudo DEBIAN_FRONTEND=noninteractive apt-get install -y mysql-server-5.6

# enable xdebug
sed -i 's/^;//' ~/.phpenv/versions/$(phpenv global)/etc/conf.d/xdebug.ini

# php.ini (memory limit)
cp build/circleci/php.ini ~/.phpenv/versions/$(phpenv global)/etc/conf.d/

# warmup composer dist packages
composer install --prefer-dist --no-interaction --quiet

# Prepare magento connect download
(
    composer global config http-basic.repo.magento.com "${MAGENTO_CONNECT_USERNAME}" "${MAGENTO_CONNECT_PASSWORD}"
)

# on circleci, the magento installation itself counts as a dependency as assets and it can be cached
buildecho "install magento incl. sampledata with the installer:"
magerun_install "${MAGENTO_VERSION}" "${INSTALL_SAMPLE_DATA}"
