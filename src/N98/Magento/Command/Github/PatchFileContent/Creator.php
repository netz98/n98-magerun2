<?php
/**
 * @copyright Copyright (c) netz98 GmbH (https://www.netz98.de)
 *
 * @see PROJECT_LICENSE.txt
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
    public static function create(string $diffContent): string
    {
        $appDesignProcessor = new AppDesignProcessor();
        $diffContent = $appDesignProcessor->process($diffContent);

        $appCodeProcessor = new AppCodeProcessor();
        $diffContent = $appCodeProcessor->process($diffContent);

        $i18nProcessor = new I18nProcessor();
        $diffContent = $i18nProcessor->process($diffContent);

        $libProcessor = new LibProcessor();
        $diffContent = $libProcessor->process($diffContent);

        return $diffContent;
    }
}
