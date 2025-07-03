<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace N98\Magento\Command\Github\PatchFileContent\Processor;

class AppCodeProcessor implements ProcessorInterface
{
    public function process(string $diffContent, string $replaceVendor): string
    {
        $callback = function ($matches) use ($replaceVendor) {
            // camelcase to dash
            $matches[1] = preg_replace('/([a-z])([A-Z])/', '$1-$2', $matches[1]);

            return 'vendor/' . $replaceVendor . '/module-' . strtolower($matches[1]) . '/';
        };

        return (string) preg_replace_callback('/app\/code\/Magento\/([a-zA-Z0-9_]+)\//', $callback, $diffContent);
    }
}
