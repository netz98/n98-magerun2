<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Test\Integration;

use N98\Magento\Command\TestCase;

/**
 * Class FlushCommandTest
 * @package N98\Magento\Command\Test\Integration
 */
class FlushCommandTest extends TestCase
{
    public function testExecute()
    {
        $this->assertDisplayContains('test:integration:flush', 'No integration tests directory found');
    }

    public function testExecuteWithForceOption()
    {
        $this->assertDisplayContains('test:integration:flush --force', 'No integration tests directory found');
    }
}