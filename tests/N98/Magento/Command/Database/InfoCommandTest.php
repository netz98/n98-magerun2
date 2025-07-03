<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

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
        $input = [
            'command' => 'db:info',
            'setting' => 'MySQL-Cli-String',
        ];

        $this->assertDisplayNotContains($input, 'MySQL-Cli-String');
        $this->assertDisplayContains($input, 'mysql -h');
    }
}
