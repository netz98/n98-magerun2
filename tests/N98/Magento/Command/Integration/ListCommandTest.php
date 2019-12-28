<?php

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
