# ddev command in container
alias install-magento-ce="/mnt/ddev_config/commands/web/install-magento-ce"
alias install-magento-ce-git="/mnt/ddev_config/commands/web/install-magento-ce-git"

# Run the development CLI against the standard Magento test installation.
alias mr2='/var/www/html/bin/n98-magerun2 --root-dir="$N98_MAGERUN2_TEST_MAGENTO_ROOT"'

# Discover installed shops whenever a container shell loads its aliases.
_mr2_register_shop_aliases() {
    local shop name command
    for shop in /opt/magento-test-environments/*; do
        [[ -f "$shop/app/bootstrap.php" ]] || continue
        name="${shop##*/}"
        [[ "$name" =~ ^[[:alnum:]_.-]+$ ]] || continue
        printf -v command '%q --root-dir=%q' /var/www/html/bin/n98-magerun2 "$shop"
        alias "mr2-$name=$command"
    done
}

_mr2_register_shop_aliases
unset -f _mr2_register_shop_aliases
