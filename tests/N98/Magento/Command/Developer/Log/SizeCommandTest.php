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
            ['command' => 'dev:log:size'],
            'Total:'
        );
    }

    public function testExecuteWithHumanReadableOption()
    {
        $this->assertDisplayContains(
            ['command' => 'dev:log:size', '--human-readable' => true],
            'Total:'
        );
    }

    public function testExecuteWithSortBySizeOption()
    {
        $this->assertDisplayContains(
            ['command' => 'dev:log:size', '--sort-by-size' => true],
            'Total:'
        );
    }

    public function testExecuteWithFilterOption()
    {
        $this->assertDisplayContains(
            ['command' => 'dev:log:size', '--filter' => 'system'],
            'Total:'
        );
    }

    public function testExecuteWithFormatOption()
    {
        $this->assertDisplayContains(
            ['command' => 'dev:log:size', '--format' => 'csv'],
            '"Log File",Size,"Last Modified"'
        );
    }
}
