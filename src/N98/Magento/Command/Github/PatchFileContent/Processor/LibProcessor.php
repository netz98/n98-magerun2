<?php

declare(strict_types=1);

namespace N98\Magento\Command\Github\PatchFileContent\Processor;

class LibProcessor implements ProcessorInterface
{
    public function process(string $diffContent): string
    {
        // edge cases -> Message Queue is part of the framework directory but later on in an own package
        $diffContent = preg_replace(
            '/lib\/internal\/Magento\/Framework\/MessageQueue\/([a-zA-Z0-9_]+)\//',
            'vendor/magento/framework-message-queue/$1/',
            $diffContent
        );

        // Handle the rest of the lib/internal/Magento directory
        $callback = function ($matches) {
            // camelcase to dash
            $matches[1] = preg_replace('/([a-z])([A-Z])/', '$1-$2', $matches[1]);

            return 'vendor/magento/' . strtolower($matches[1]) . '/';
        };

        $diffContent = (string) preg_replace_callback(
            '/lib\/internal\/Magento\/([a-zA-Z0-9_-]+)\//',
            $callback,
            $diffContent
        );

        return $diffContent;
    }
}
