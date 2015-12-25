#!/bin/bash
set -euo pipefail
IFS=$'\n\t'

buildecho()
{
    echo -en "\e[44m[CIRCLECI]\e[49m "
    echo "${1}"
}

export CLOVER_XML="${CIRCLE_ARTIFACTS:-.}/clover.xml"
buildecho "clover.xml: '${CLOVER_XML}', exported as \$CLOVER_XML."

export MAGENTO_VERSION="magento-ce-2.0.0"
export DB=mysql
export INSTALL_SAMPLE_DATA=0
export COVERAGE=10

. build/sh/magento_connect.sh
