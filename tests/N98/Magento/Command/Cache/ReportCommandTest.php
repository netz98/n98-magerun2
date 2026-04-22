<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Cache;

use N98\Magento\Command\TestCase;

class ReportCommandTest extends TestCase
{
    public function testExecute()
    {
        $command = [
            'command' => 'cache:report',
        ];

        $commandObj = $this->getApplication()->find('cache:report');
        $tester = new \Symfony\Component\Console\Tester\CommandTester($commandObj);
        $tester->execute(['command' => $commandObj->getName()]);
        
        $display = $tester->getDisplay();
        
        if (strpos($display, 'does not support getting all IDs') !== false) {
            $this->markTestSkipped('The current cache adapter does not support getting all IDs.');
        }

        $this->assertEquals(0, $tester->getStatusCode());
        $this->assertMatchesRegularExpression('~\\| ID.*\\| EXPIRE.*\\|$~m', $display);
    }
}
