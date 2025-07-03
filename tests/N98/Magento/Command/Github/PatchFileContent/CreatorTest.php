<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace N98\Magento\Command\Github\PatchFileContent;

use PHPUnit\Framework\TestCase;

class CreatorTest extends TestCase
{
    public function testCreateWithVendorMagento()
    {
        $diffContent = 'app/code/Magento/SampleModule/';
        $expectedResult = 'vendor/magento/module-sample-module/';

        $result = Creator::create($diffContent, 'magento');

        $this->assertEquals($expectedResult, $result);
    }

    public function testCreateWithVendorMageOS()
    {
        $diffContent = 'app/code/Magento/SampleModule/';
        $expectedResult = 'vendor/mage-os/module-sample-module/';

        $result = Creator::create($diffContent, 'mage-os');

        $this->assertEquals($expectedResult, $result);
    }
}
