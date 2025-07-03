<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace N98\Magento\Command\Github\PatchFileContent\Processor;

use PHPUnit\Framework\TestCase;

class LibProcessorTest extends TestCase
{
    public function testProcess()
    {
        $diffContent = <<<'DIFF'
                       app/i18n/de_DE/composer.json
                       app/i18n/zh_Hans_CN/language.xml
                       DIFF;

        $expectedResult = <<<'RESULT'
                        vendor/magento/language-de_de/composer.json
                        vendor/magento/language-zh_hans_cn/language.xml
                        RESULT;

        $processor = new I18nProcessor();

        $this->assertEquals($expectedResult, $processor->process($diffContent, 'magento'));
    }
}
