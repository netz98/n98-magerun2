#!/usr/bin/env bash

set -o errexit

# Basic tools

set -x

# bats (for testing)
git clone --branch v1.2.1 https://github.com/bats-core/bats-core.git /tmp/bats-core && pushd /tmp/bats-core >/dev/null && sudo ./install.sh /usr/local

sudo apt-get update --allow-releaseinfo-change
sudo apt-get -y install zstd lz4 sendmail mydumper

# Show info to simplify debugging
lsb_release -a
