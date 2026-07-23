<?php

namespace N98\Magento\Mcp;

use N98\Magento\Application;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CommandToolHandlerTest extends TestCase
{
    public function testInvokePassesCommandNameToInput()
    {
        $command = new class('proxy:command') extends Command {
            /** @var InputInterface|null */
            public $capturedInput;

            protected function configure(): void
            {
                $this->addArgument('foo', InputArgument::OPTIONAL);
            }

            protected function execute(InputInterface $input, OutputInterface $output): int
            {
                $this->capturedInput = $input;
                $output->writeln((string) $input->getArgument('foo'));

                return 0;
            }
        };

        $application = new Application();
        $application->setAutoExit(false);
        $application->add($command);

        $handler = new CommandToolHandler($application, 'proxy:command');
        $result = $handler('bar');

        $this->assertSame('bar', $result);
        $this->assertNotNull($command->capturedInput);
        $this->assertSame("'proxy:command' --no-interaction bar", (string) $command->capturedInput);
    }
}
