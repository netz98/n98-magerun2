<?php

declare(strict_types=1);

namespace N98\Magento\Command\Github\PatchFileContent;

use PHPUnit\Framework\TestCase;

class CreatorTest extends TestCase
{
    public function testCreate()
    {
        $diffContent = 'app/code/Magento/SampleModule/';
        $expectedResult = 'vendor/magento/module-sample-module/';

        $result = Creator::create($diffContent);

        $this->assertEquals($expectedResult, $result);
    }
}
