<?php

namespace N98\Magento\Command\System;

use N98\Magento\Command\TestCase;

class InfoCommandTest extends TestCase
{
    const INSTALL_DATE_INFO = 'Install Date';
    const CRYPT_KEY_INFO = 'Crypt Key';

    /**
     * @var $command InfoCommand
     */
    private $command;

    protected function setUp(): void
    {
        /**
         * @var $command InfoCommand
         */
        $this->command = $this->getApplication()->find('sys:info');

        // The command is executed here
        $this->assertExecute('sys:info');
    }

    public function testInstallDate()
    {
        $this->assertNotFalse(strtotime($this->command->getInfo(self::INSTALL_DATE_INFO)));
    }

    public function testCryptKey()
    {
        $this->assertMatchesRegularExpression('/^[a-f0-9]{32}$/', $this->command->getInfo(self::CRYPT_KEY_INFO));
    }

    public function testCounts()
    {
        $counts = ['Admin User Count', 'Attribute Count', 'Customer Count', 'Category Count', 'Product Count'];

        $this->assertEmpty(array_diff($counts, array_keys($this->command->getInfo())));
    }
}
