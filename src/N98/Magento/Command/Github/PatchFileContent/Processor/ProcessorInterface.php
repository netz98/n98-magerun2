<?php

declare(strict_types=1);

namespace N98\Magento\Command\Github\PatchFileContent\Processor;

interface ProcessorInterface
{
    public function process(string $diffContent, string $replaceVendor): string;
}
