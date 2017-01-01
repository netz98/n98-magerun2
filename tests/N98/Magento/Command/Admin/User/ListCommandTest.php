<?php

namespace N98\Magento\Command\Admin\User;

use N98\Magento\Command\TestCase;

class ListCommandTest extends TestCase
{
    /**
     * @group current
     */
    public function testExecute()
    {
        $this->assertDisplayContains('admin:user:list', 'id');
        $this->assertDisplayContains('admin:user:list', 'user');
        $this->assertDisplayContains('admin:user:list', 'email');
        $this->assertDisplayContains('admin:user:list', 'status');
    }
}
