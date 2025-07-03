<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\System\Setup;

use N98\Magento\Command\TestCase;
use org\bovigo\vfs\vfsStream;

class CompareVersionsCommandTest extends TestCase
{
    public function testExecute()
    {
        $this->assertDisplayContains('sys:setup:compare-versions', 'Setup');
        $this->assertDisplayContains('sys:setup:compare-versions', 'Module');
        $this->assertDisplayContains('sys:setup:compare-versions', 'DB');
        $this->assertDisplayContains('sys:setup:compare-versions', 'Data');
        $this->assertDisplayContains('sys:setup:compare-versions', 'Status');
    }

    public function testJunit()
    {
        vfsStream::setup();
        $url = vfsStream::url('root/junit.xml');

        $this->assertExecute(
            [
                'command'     => 'sys:setup:compare-versions',
                '--log-junit' => $url,
            ]
        );
        $this->assertFileExists($url);
    }
}
