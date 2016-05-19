<?php

namespace N98\Magento\Command\Database\Maintain;

use Symfony\Component\Console\Tester\CommandTester;
use N98\Magento\Command\PHPUnit\TestCase;

/**
 * @see \N98\Magento\Command\Database\Maintain\CheckTablesCommand
 */
class CheckTablesTest extends TestCase
{
    public function testExecuteMyIsam()
    {
        $this->markTestSkipped('Currently we have no myisam tables in a magento2 installation');

        $command = $this->getCommand();

        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array(
                'command'  => $command->getName(),
                '--format' => 'csv',
                '--type'   => 'quick',
                '--table'  => 'oauth_nonce'
            )
        );
        $this->assertContains('oauth_nonce,check,quick,OK', $commandTester->getDisplay());

    }

    public function testExecuteInnoDb()
    {
        $command = $this->getCommand();

        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array(
                'command'  => $command->getName(),
                '--format' => 'csv',
                '--type'   => 'quick',
                '--table'  => 'catalog_product_entity_media_gallery*'
            )
        );
        $timeRegex = '"\s+[0-9]+\srows","[0-9\.]+\ssecs"';
        $this->assertRegExp(
            '~catalog_product_entity_media_gallery,"ENGINE InnoDB",' . $timeRegex . '~',
            $commandTester->getDisplay()
        );
        $this->assertRegExp(
            '~catalog_product_entity_media_gallery_value,"ENGINE InnoDB",' . $timeRegex . '~',
            $commandTester->getDisplay()
        );
    }

    /**
     * @return \Symfony\Component\Console\Command\Command
     */
    protected function getCommand()
    {
        $application = $this->getApplication();
        $application->add(new CheckTablesCommand());
        $command = $this->getApplication()->find('db:maintain:check-tables');

        return $command;
    }
}
