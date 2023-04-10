<?php
/**
 * @copyright Copyright (c) netz98 GmbH (https://www.netz98.de)
 *
 * @see PROJECT_LICENSE.txt
 */

declare(strict_types=1);

namespace N98\Magento\Command\Github;

use N98\Util\OperatingSystem;

class PatchFileContentCreator
{
    /**
     * @return string
     */
    public static function create(array $prData, string $diffContent): string
    {
        $diffContent = self::processAppCode($diffContent);
        $diffContent = self::processAppDesign($diffContent);

        return $diffContent;
    }

    /**
     * @param string $diffContent
     * @return string
     */
    protected static function processAppCode(string $diffContent): string
    {
        $callback = function ($matches) {
            // camelcase to dash
            $matches[1] = preg_replace('/([a-z])([A-Z])/', '$1-$2', $matches[1]);

            return 'vendor/magento/module-' . strtolower($matches[1]) . '/';
        };

        return (string) preg_replace_callback('/app\/code\/Magento\/([a-zA-Z0-9_]+)\//', $callback, $diffContent);
    }

    /**
     * @param string $diffContent
     * @return string
     */
    protected static function processAppDesign($diffContent): string
    {
        // preg_replace app/design/frontend/Magento/<blank>/ with vendor/magento/theme-frontend-<blank>/
        $diffContent = preg_replace(
            '/app\/design\/frontend\/Magento\/([a-zA-Z0-9_]+)\//',
            'vendor/magento/theme-frontend-$1/',
            $diffContent
        );

        // preg_replace app/design/adminhtml/Magento/<blank>/ with vendor/magento/theme-adminhtml-<blank>/
        $diffContent = preg_replace(
            '/app\/design\/adminhtml\/Magento\/([a-zA-Z0-9_]+)\//',
            'vendor/magento/theme-adminhtml-$1/',
            $diffContent
        );

        return $diffContent;
    }
}
