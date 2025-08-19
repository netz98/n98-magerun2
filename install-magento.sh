#!/bin/bash

PHP_BIN="php"
COMPOSER_BIN="/usr/local/bin/composer"
MAGENTO_VERSION="2.4.5"
MAGENTO_ROOT_DIR="/tmp/magento2"

if [ -d "$MAGENTO_ROOT_DIR" ]; then
    echo "Magento in $MAGENTO_ROOT_DIR already exists. Skip installation"
    exit 0
fi

if [ ! -d $MAGENTO_ROOT_DIR ]; then
    "$PHP_BIN" $COMPOSER_BIN --no-interaction create-project --repository-url=https://repo.magento.com/ magento/project-community-edition="$MAGENTO_VERSION" "$MAGENTO_ROOT_DIR"
fi

if [ ! -d $MAGENTO_ROOT_DIR ]; then
  echo "Magento installation failed"
  exit 1;
fi

cd $MAGENTO_ROOT_DIR || exit 1

if [ -d "./generated" ]; then
    rm -Rf ./generated
fi

# We can't install Magento because we don't have a database.
# We will just have the files.
# This should be enough for the command to run.
echo "Magento downloaded to $MAGENTO_ROOT_DIR"
