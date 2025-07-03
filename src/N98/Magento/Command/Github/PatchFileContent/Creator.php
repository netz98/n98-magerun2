<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace N98\Magento\Command\Github\PatchFileContent;

use N98\Magento\Command\Github\PatchFileContent\Processor\AppCodeProcessor;
use N98\Magento\Command\Github\PatchFileContent\Processor\AppDesignProcessor;
use N98\Magento\Command\Github\PatchFileContent\Processor\I18nProcessor;
use N98\Magento\Command\Github\PatchFileContent\Processor\LibProcessor;

class Creator
{
    /**
     * @param string $diffContent
     * @return string
     */
    public static function create(string $diffContent, string $replaceVendor): string
    {
        $appDesignProcessor = new AppDesignProcessor();
        $diffContent = $appDesignProcessor->process($diffContent, $replaceVendor);

        $appCodeProcessor = new AppCodeProcessor();
        $diffContent = $appCodeProcessor->process($diffContent, $replaceVendor);

        $i18nProcessor = new I18nProcessor();
        $diffContent = $i18nProcessor->process($diffContent, $replaceVendor);

        $libProcessor = new LibProcessor();
        $diffContent = $libProcessor->process($diffContent, $replaceVendor);

        return $diffContent;
    }
}
