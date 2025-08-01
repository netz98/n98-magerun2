<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Developer\Log;

use N98\Magento\Command\TestCase;

class SizeCommandTest extends TestCase
{
    public function testExecute()
    {
        $this->assertDisplayContains(
            ['dev:log:size'],
            ['Total:', 'log files']
        );
    }

    public function testExecuteWithHumanReadableOption()
    {
        $this->assertDisplayContains(
            ['dev:log:size', '--human-readable'],
            'Total:'
        );
    }

    public function testExecuteWithSortBySizeOption()
    {
        $this->assertDisplayContains(
            ['dev:log:size', '--sort-by-size'],
            'Total:'
        );
    }

    public function testExecuteWithFilterOption()
    {
        $this->assertDisplayContains(
            ['dev:log:size', '--filter', 'system'],
            'Total:'
        );
    }
}