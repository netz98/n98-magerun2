<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Integration;

use N98\Magento\Command\TestCase;

/**
 * Class ListCommandTest
 * @package N98\Magento\Command\Script\Repository
 */
class ListCommandTest extends TestCase
{
    public function testExecute()
    {
        $this->assertDisplayContains('integration:list', 'email');
        $this->assertDisplayContains('integration:list', 'endpoint');
    }
}
