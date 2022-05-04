<?php
/*
 * this file is part of magerun
 */

namespace N98\Util;

/**
 * Class OperatingSystem
 *
 * @package N98\Util
 */
class PharWrapper
{
    /**
     * Some dummy method to let the autoloader load the class before it's unregistered
     */
    public static function init()
    {}

    /**
     * Magento 2.3.1 removes the phar wrapper so we re-register it
     */
    public static function ensurePharWrapperIsRegistered()
    {
        // Magento 2.3.1 removes phar stream wrapper.
        if (!in_array('phar', \stream_get_wrappers(), true)) {
            if (!\stream_wrapper_restore('phar')) {
                stream_wrapper_register('phar', \TYPO3\PharStreamWrapper\PharStreamWrapper::class);
            }
        }
    }
}
