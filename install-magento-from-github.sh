#!/bin/bash

sudo apt-get update
sudo apt-get install -y php-bcmath php-gd php-intl php-mysql php-soap

MAGENTO_ROOT_DIR="/tmp/magento2"

if [ -d "$MAGENTO_ROOT_DIR" ]; then
    echo "Magento in $MAGENTO_ROOT_DIR already exists. Skip installation"
    exit 0
fi

git clone https://github.com/magento/magento2.git "$MAGENTO_ROOT_DIR"

cd "$MAGENTO_ROOT_DIR" || exit 1

php -d memory_limit=-1 /usr/local/bin/composer update --no-interaction
