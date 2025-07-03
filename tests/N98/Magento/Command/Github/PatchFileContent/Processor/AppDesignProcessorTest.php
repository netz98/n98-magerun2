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

class AppDesignProcessorTest extends TestCase
{
    public function testProcess()
    {
        $processor = new AppDesignProcessor();

        $diffContent = 'app/design/frontend/Magento/sampletheme/';
        $expectedResult = 'vendor/magento/theme-frontend-sampletheme/';

        $this->assertSame(
            $expectedResult,
            $processor->process($diffContent, 'magento'),
        );
    }
}
