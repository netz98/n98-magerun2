<?php

namespace N98\Magento\Command\Database;

use N98\Magento\Command\TestCase;

class InfoCommandTest extends TestCase
{
    public function testExecute()
    {
        $this->assertDisplayContains('db:info', 'PDO-Connection-String');
    }

    public function testExecuteWithSettingArgument()
    {
        $input = array(
            'command' => 'db:info',
            'setting' => 'MySQL-Cli-String',
        );

        $this->assertDisplayNotContains($input, 'MySQL-Cli-String');
        $this->assertDisplayContains($input, 'mysql -h');
    }
}
