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
export MAGENTO_CONNECT_USERNAME=adba1dac261c621b4b8d154da0d74f15
export MAGENTO_CONNECT_PASSWORD=096d8c703c14d84af9980fe040e014d9
