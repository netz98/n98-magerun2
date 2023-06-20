<?php
/**
 * @copyright Copyright (c) netz98 GmbH (https://www.netz98.de)
 *
 * @see PROJECT_LICENSE.txt
 */

declare(strict_types=1);

namespace N98\Magento\Command\Github\PatchFileContent\Processor;

class I18nProcessor implements ProcessorInterface
{
    public function process(string $diffContent): string
    {
        $diffContent = preg_replace_callback(
            '/app\/i18n\/([a-zA-Z0-9_]+)\//',
            function ($matches) {
                return 'vendor/magento/language-' . strtolower($matches[1]) . '/';
            },
            $diffContent
        );

        return $diffContent;
    }
}
