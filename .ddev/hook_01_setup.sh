#!/bin/bash

# exit if an error occure
set -e

CURRENT_DIR=$(dirname "$0")
source "${CURRENT_DIR}/bash/helpers/colors.sh"
INSTALL_MAGENTO_CE_CMD="${CURRENT_DIR}/commands/web/install-magento-ce"

function configure_composer_help() {
    echo -e "${txtred}=========================="
    echo -e "> STOP!"
    echo -e "==========================${txtrst}"
    echo ""
    echo -e "${txtylw}\$MAGENTO_REPO_USERNAME${txtrst} and ${txtylw}\$MAGENTO_REPO_PASSWORD${txtrst} environment variables are not set."
    echo ""
    echo -e "See: ${txtblu}https://ddev.readthedocs.io/en/stable/users/extend/customization-extendibility/#providing-custom-environment-variables-to-a-container${txtrst}"
    echo ""

    exit 1
}

function setup_composer() {
    if [ -z "$MAGENTO_REPO_USERNAME" ]; then
        configure_composer_help
    fi

    if [ -z "$MAGENTO_REPO_PASSWORD" ]; then
        configure_composer_help
    fi

    composer -q --ansi global config http-basic.repo.magento.com "$MAGENTO_REPO_USERNAME" "$MAGENTO_REPO_PASSWORD"
    echo -en "${txtgrn}${check_mark} Magento repo configured ${txtrst} \n"
}

function setup_test_magento_environments() {
    echo -e "${txtblu}=========================================================="
    echo -e "> Install Magento Test Environments"
    echo -e "==========================================================${txtrst}"

    sudo chown -R "$(id -u):$(id -g)" /opt/magento-test-environments

    # only with older PHP versions
    #$INSTALL_MAGENTO_CE_CMD "2.3.7-p3" no

    # Change version in .ddev/config.yaml
    $INSTALL_MAGENTO_CE_CMD "$MAGERUN_SETUP_TEST_DEFAULT_MAGENTO_VERSION" yes
}

function setup_bats() {
    echo -e "${txtblu}=========================================================="
    echo -e "> Install Bats"
    echo -e "==========================================================${txtrst}"

    if [ ! -f /usr/local/bin/bats ]; then
      git clone --branch v1.2.1 https://github.com/bats-core/bats-core.git /tmp/bats-core \
        && pushd /tmp/bats-core >/dev/null \
        && sudo ./install.sh /usr/local
    fi
}

function setup_bats() {
    echo -e "${txtblu}=========================================================="
    echo -e "> Development setup completed"
    echo -e "==========================================================${txtrst}"

    echo "You can now login to your dev environment by using 'ddev ssh'"
    echo "Then you can run:"
    echo "  bin/n98-magerun2 --root-dir=/opt/magento-test-environments/magento_<version>"
}

function setup_success() {
    echo -e "${txtgrn}${check_mark} Setup completed ${txtrst}"
}

setup_composer
setup_test_magento_environments
setup_bats
setup_success
