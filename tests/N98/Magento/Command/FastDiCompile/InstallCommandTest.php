<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace N98\Magento\Command\FastDiCompile;

use PHPUnit\Framework\TestCase;

class InstallCommandTest extends TestCase
{
    public function testCommandIsConfigured(): void
    {
        $command = new InstallCommand();

        $this->assertSame('fast-di-compile:install', $command->getName());
        $this->assertSame('Install the fast DI compiler replacement', $command->getDescription());
        $this->assertTrue($command->getDefinition()->hasOption('no-setup-upgrade'));
    }
}
