<?php

namespace N98\Magento\Command\System;

use Symfony\Component\Console\Tester\CommandTester;
use N98\Magento\Command\PHPUnit\TestCase;

class InfoCommandTest extends TestCase
{
    const INSTALL_DATE_INFO = 'Install Date';
    const CRYPT_KEY_INFO = 'Crypt Key';

    /**
     * @var $command InfoCommand
     */
    protected $command = null;

    public function setUp()
    {
        $application = $this->getApplication();
        $application->add(new InfoCommand());

        /**
         * @var $command InfoCommand
         */
        $this->command = $this->getApplication()->find('sys:info');

        // The command is executed here
        $commandTester = new CommandTester($this->command);
        $commandTester->execute(array('command' => $this->command->getName()));
    }

    public function testInstallDate()
    {
        $this->assertNotFalse(strtotime($this->command->getInfo(self::INSTALL_DATE_INFO)));
    }

    public function testCryptKey()
    {
        $this->assertNotFalse(preg_match('/^[a-f0-9]{32}$/', $this->command->getInfo(self::CRYPT_KEY_INFO)));
    }

    public function testCounts()
    {
        $counts = ['Attribute Count', 'Customer Count', 'Category Count', 'Product Count'];

        $this->assertEmpty(array_diff($counts, array_keys($this->command->getInfo())));
    }
}
