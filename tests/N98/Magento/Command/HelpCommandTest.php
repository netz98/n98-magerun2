<?php

namespace N98\Magento\Command;

class HelpCommandTest extends TestCase
{
    public function testExecute()
    {
        $this->assertDisplayContains('help', 'The help command displays help for a given command');
    }
}
