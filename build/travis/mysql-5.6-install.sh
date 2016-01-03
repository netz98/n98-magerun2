#!/bin/bash
set -euo pipefail
IFS=$'\n\t'

#
# Install MySQL 5.6 on travis
#

sudo apt-get update -qq;
sudo apt-get install -qq mysql-server-5.6 mysql-client-5.6 mysql-client-core-5.6;
mysql -uroot -e 'SET @@global.sql_mode = NO_ENGINE_SUBSTITUTION; CREATE DATABASE magento_travis;';
