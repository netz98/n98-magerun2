<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace N98\Magento\Application\Console\Descriptor;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;

class MagerunTextDescriptorTest extends TestCase
{
    public function testCommandMetadataCannotBreakDecoratedListFormatting(): void
    {
        $application = new Application('test');
        $application->add(new Command('broken-command'));
        $application->get('broken-command')->setDescription('Broken </muted><accent>command metadata');

        $output = new BufferedOutput(OutputInterface::VERBOSITY_NORMAL, true);

        (new MagerunTextDescriptor())->describe($output, $application);

        $this->assertStringContainsString('Broken </muted><accent>command metadata', $output->fetch());
    }
}
