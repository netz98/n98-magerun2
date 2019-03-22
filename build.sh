#!/bin/bash
#
# build from clean checkout
#
# usage: ./build.sh from project root
set -euo pipefail
IFS=$'\n\t'

exit_trap() {
  local status=$?
  if [[ -d "${base_dir}/${build_dir}" ]]; then
    echo "trap: removing '${build_dir}'.."
    rm -rf "${base_dir}/${build_dir}"
  fi
  echo "exit ($status)."
}

establish_build_dir() {
  local build_dir="${1}"
  rm -rf "${build_dir}"
  if [[ -d "${build_dir}" ]]; then
    >&2 echo "Error: Can not remove build-dir '${build_dir}'"
    echo "aborting."
    exit 1
  fi
  mkdir "${build_dir}"
  if [[ ! -d "${build_dir}" ]]; then
    >&2 echo "Error: Can not create build-dir '${build_dir}'"
    echo "aborting."
    exit 1
  fi
}

name="$(perl -ne '/<project name="([^"]*)"/ and print $1 and last' build.xml)"
nice_name="$(php -r "echo str_replace(' ', '', ucwords(strtr('${name}', '-', ' ')));")"
phar="${name}.phar"
echo "Building ${phar}..."

base_dir="$(pwd -P)"
build_dir="build/output"

echo "$0 executed in ${base_dir}"

trap exit_trap EXIT

establish_build_dir "${build_dir}"

git clone --quiet --no-local --depth 1 -- . "${build_dir}"

composer_bin="${base_dir}/vendor/bin/composer"
phing_bin="${base_dir}/vendor/bin/phing"

# Set COMPOSER_HOME if HOME and COMPOSER_HOME not set (shell with no home-dir, e.g. build server with webhook)
if [[ -z ${HOME+x} && -z ${COMPOSER_HOME+x} ]]; then
  echo "provision: create COMPOSER_HOME directory for composer (no HOME)"
  mkdir -p "build/composer-home"
  export COMPOSER_HOME="$(pwd -P)/build/composer-home"
fi

# build systems that do not have a composer install running get one for free
if [[ ! -f "${phing_bin}" ]]; then
    echo "provision: download composer.phar and install build dependencies ..."
    composer="composer.phar"
    rm -rf vendor
    wget -q -O "${composer}" https://getcomposer.org/download/1.3.2/composer.phar
    chmod +x "${composer}"
    php -f "${composer}" -- --version
    php -f "${composer}" -- --profile -q install --prefer-dist --no-interaction --ignore-platform-reqs
    rm "${composer}"
fi

echo "with: $(php --version|head -n 1)"
echo "with: $("${composer_bin}" --version)"
echo "with: $("${phing_bin}" -version)"

cd "${build_dir}"

echo "building in $(pwd -P)"
echo "build version: $(git --no-pager log --oneline -1)"

echo "provision: ulimits (soft) set from $(ulimit -Sn) to $(ulimit -Hn) (hard) for faster phar builds..."
ulimit -Sn $(ulimit -Hn)
timestamp="$(git log --format=format:%ct HEAD -1)" # reproduceable build
echo "build timestamp: ${timestamp}"

php -f "${phing_bin}" -dphar.readonly=0 -- \
  -Dcomposer_suffix="${nice_name}${timestamp}" \
  -Dcomposer_bin="${composer_bin}" \
  dist_clean

php -f build/phar/phar-timestamp.php

php -f "${phar}" -- --version
ls -al "${phar}"

cd -
cp -vp "${build_dir}"/"${phar}" "${phar}"
rm -rf "${build_dir}"

trap - EXIT

echo "done."
