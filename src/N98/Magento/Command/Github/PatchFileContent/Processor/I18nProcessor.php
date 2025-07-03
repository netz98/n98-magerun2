<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace N98\Magento\Command\Github\PatchFileContent\Processor;

class I18nProcessor implements ProcessorInterface
{
    public function process(string $diffContent, string $replaceVendor): string
    {
        $diffContent = preg_replace_callback(
            '/app\/i18n\/([a-zA-Z0-9_]+)\//',
            function ($matches) use ($replaceVendor) {
                return 'vendor/' . $replaceVendor . '/language-' . strtolower($matches[1]) . '/';
            },
            $diffContent
        );

        return $diffContent;
    }
}
