<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace N98\Magento\Command\Github\PatchFileContent\Processor;

class AppDesignProcessor implements ProcessorInterface
{
    public function process(string $diffContent, string $replaceVendor): string
    {
        // preg_replace app/design/frontend/Magento/<blank>/ with vendor/magento/theme-frontend-<blank>/
        $diffContent = preg_replace(
            '/app\/design\/frontend\/Magento\/([a-zA-Z0-9_]+)\//',
            'vendor/' . $replaceVendor . '/theme-frontend-$1/',
            $diffContent
        );

        // preg_replace app/design/adminhtml/Magento/<blank>/ with vendor/magento/theme-adminhtml-<blank>/
        $diffContent = preg_replace(
            '/app\/design\/adminhtml\/Magento\/([a-zA-Z0-9_]+)\//',
            'vendor/' . $replaceVendor . '/theme-adminhtml-$1/',
            $diffContent
        );

        return $diffContent;
    }
}
