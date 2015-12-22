#!/bin/bash
#
# Install MySQL 5.6 on travis
#

sudo apt-get remove --purge mysql-common mysql-server-5.5 mysql-server-core-5.5 mysql-client-5.5 mysql-client-core-5.5;
sudo apt-get autoremove;
sudo apt-get autoclean;
sudo apt-add-repository ppa:ondrej/mysql-5.6 -y;
sudo apt-get update -qq;
sudo apt-get install -qq mysql-server-5.6 mysql-client-5.6 mysql-client-core-5.6;
mysql -uroot -e 'SET @@global.sql_mode = NO_ENGINE_SUBSTITUTION; CREATE DATABASE magento_travis;';
