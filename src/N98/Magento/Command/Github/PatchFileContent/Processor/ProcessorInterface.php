<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace N98\Magento\Command\Github\PatchFileContent\Processor;

interface ProcessorInterface
{
    public function process(string $diffContent, string $replaceVendor): string;
}
