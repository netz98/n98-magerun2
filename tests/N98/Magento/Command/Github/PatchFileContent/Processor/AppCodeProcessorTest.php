<?php
/**
 * @copyright Copyright (c) netz98 GmbH (https://www.netz98.de)
 *
 * @see PROJECT_LICENSE.txt
 */

declare(strict_types=1);

namespace N98\Magento\Command\Github\PatchFileContent\Processor;

use PHPUnit\Framework\TestCase;

class AppCodeProcessorTest extends TestCase
{
    public function testProcess()
    {
        $diffContent = 'app/code/Magento/SampleModule/';
        $expectedResult = 'vendor/magento/module-sample-module/';

        $processor = new AppCodeProcessor();

        $this->assertEquals($expectedResult, $processor->process($diffContent));
    }
}
