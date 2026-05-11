<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Database;

use PHPUnit\Framework\TestCase;

class PrepareUpgradeCommandTest extends TestCase
{
    public function testCommandConfiguration()
    {
        $command = new PrepareUpgradeCommand();
        $this->assertSame('setup:prepare-upgrade', $command->getName());

        $definition = $command->getDefinition();
        $this->assertTrue($definition->hasOption('original-db'));
        $this->assertTrue($definition->hasOption('output-file'));
        $this->assertTrue($definition->hasOption('no-data-diff'));
        $this->assertTrue($definition->hasOption('compare-extra-arg'));
        $this->assertTrue($definition->hasOption('connection'));
    }

    public function testOutputFileOptionHasShortcut()
    {
        $command = new PrepareUpgradeCommand();
        $option = $command->getDefinition()->getOption('output-file');
        $this->assertSame('o', $option->getShortcut());
    }

    public function testCompareExtraArgOptionIsArray()
    {
        $command = new PrepareUpgradeCommand();
        $option = $command->getDefinition()->getOption('compare-extra-arg');
        $this->assertTrue($option->acceptValue());
        $this->assertTrue($option->isArray());
    }

    public function testNoDataDiffOptionIsFlag()
    {
        $command = new PrepareUpgradeCommand();
        $option = $command->getDefinition()->getOption('no-data-diff');
        $this->assertFalse($option->acceptValue());
    }

    public function testOriginalDbOptionAcceptsValue()
    {
        $command = new PrepareUpgradeCommand();
        $option = $command->getDefinition()->getOption('original-db');
        $this->assertTrue($option->acceptValue());
    }

    public function testCommandDescription()
    {
        $command = new PrepareUpgradeCommand();
        $this->assertStringContainsString('original DB', $command->getDescription());
    }

    public function testCommandHelp()
    {
        $command = new PrepareUpgradeCommand();
        $help = $command->getHelp();
        $this->assertStringContainsString('setup:upgrade', $help);
        $this->assertStringContainsString('--original-db', $help);
        $this->assertStringContainsString('does <comment>not</comment> clone or import databases', $help);
        $this->assertStringContainsString('mysqldbcompare', $help);
        $this->assertStringContainsString('staging/CI', $help);
    }
}
