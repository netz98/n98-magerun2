<?php
/**
 * Minimal bootstrap file for n98-magerun2 tests
 */

// Define the Magento base path (BP)
// N98_MAGERUN2_TEST_MAGENTO_ROOT is set to /app in the test environment
if (!defined('BP')) {
    define('BP', '/app');
}

// Attempt to include the main autoloader for n98-magerun2.
// This might help with some class loading, but won't provide Magento framework classes.
// The Magento2Initializer will attempt to load Magento's own autoloader from vendor/autoload.php
// relative to BP if the AutoloaderRegistry class is not found.
if (file_exists(BP . '/vendor/autoload.php')) {
    // This is n98-magerun2's autoloader, not Magento's.
    // A real Magento instance would have its own vendor/autoload.php here.
    require_once BP . '/vendor/autoload.php';
}
