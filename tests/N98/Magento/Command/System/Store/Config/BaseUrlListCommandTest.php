<?php

namespace N98\Magento\Command\System\Store\Config;

use N98\Magento\Command\TestCase;

class BaseUrlListCommandTest extends TestCase
{
    public function testExecute()
    {
        $this->assertDisplayContains('sys:store:config:base-url:list', 'id');
        $this->assertDisplayContains('sys:store:config:base-url:list', 'code');
        $this->assertDisplayContains('sys:store:config:base-url:list', 'unsecure_baseurl');
        $this->assertDisplayContains('sys:store:config:base-url:list', 'secure_baseurl');
    }
}
