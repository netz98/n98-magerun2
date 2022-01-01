#!/bin/bash
#
# build from clean checkout
#
# usage: ./build.sh from project root
set -euo pipefail
IFS=$'\n\t'

if [ ! -f box.phar ]; then
  curl -L https://github.com/box-project/box/releases/download/3.14.0/box.phar -o box.phar
  chmod +x ./box.phar
fi

BOX_BIN="./box.phar";
phar="n98-magerun2.phar";

if command -v composer &> /dev/null
then
	composer_bin="composer"
else
	echo "Composer was not found. Try to install it ..."
	# install composer
	php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
	php composer-setup.php
	composer_bin="${base_dir}/composer.phar"
fi

#composer dump-autoload
#patch -p1 < build/phar/patches/composer_autoloader.patch

echo "with: $(php --version|head -n 1)"
echo "with: $("${composer_bin}" --version)"
echo "with: $("${BOX_BIN}" --version)"
echo "build version: $(git --no-pager log --oneline -1)"
echo "provision: ulimits (soft) set from $(ulimit -Sn) to $(ulimit -Hn) (hard) for faster phar builds..."
if [ "$(uname -s)" != "Darwin" ]; then
  ulimit -Sn $(ulimit -Hn)
fi

timestamp="$(git log --format=format:%ct HEAD -1)" # reproducible build
echo "build timestamp: ${timestamp}"

$BOX_BIN compile;

php -f build/phar/phar-timestamp.php -- $timestamp

php -f "${phar}" -- --version
ls -al "${phar}"

chmod +x ${phar}

$BOX_BIN verify ${phar};

echo "done."
