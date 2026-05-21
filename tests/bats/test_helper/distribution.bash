is_mage_os_distribution() {
  local composer_lock
  composer_lock="$N98_MAGERUN2_TEST_MAGENTO_ROOT/composer.lock"

  [ -f "$composer_lock" ] && grep -q '"name": "mage-os/' "$composer_lock"
}
