<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Developer\Theme;

use N98\Magento\Command\TestCase;

class BuildHyvaThemeCommandTest extends TestCase
{
    public function testExecute()
    {
        $this->markTestSkipped('This test is skipped because it requires the Hyva theme to be installed.');
        $this->assertDisplayContains('dev:theme:build-hyva', 'Rebuilding...');
    }
}
