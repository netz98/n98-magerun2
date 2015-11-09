#!/usr/bin/env bash

# Install MySQL 5.6
sudo apt-get remove --purge mysql-common mysql-server-5.5 mysql-server-core-5.5 mysql-client-5.5 mysql-client-core-5.5;
sudo apt-get autoremove;
sudo apt-get autoclean;
sudo apt-add-repository ppa:ondrej/mysql-5.6 -y;
sudo apt-get update;
sudo apt-get install mysql-server-5.6 mysql-client-5.6;
mysql -uroot -e 'SET @@global.sql_mode = NO_ENGINE_SUBSTITUTION; CREATE DATABASE magento_travis;';

# Install Magento with install command
bin/n98-magerun2 install --magentoVersionByName="${MAGENTO_VERSION}" --installationFolder="./${MAGENTO_VERSION}" --dbHost=localhost --dbUser=root --dbPass='' --dbName="magento_travis" --installSampleData=${INSTALL_SAMPLE_DATA} --useDefaultConfigParams=yes --baseUrl="http://travis.magento.local/"

# Prepare test framework
export N98_MAGERUN2_TEST_MAGENTO_ROOT="./${MAGENTO_VERSION}"
