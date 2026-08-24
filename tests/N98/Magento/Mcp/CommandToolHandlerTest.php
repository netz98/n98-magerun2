<?php

namespace N98\Magento\Mcp;

use Mcp\Exception\ToolCallException;
use N98\Magento\Application;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CommandToolHandlerTest extends TestCase
{
    public function testInvokePassesSingleWordArgumentToInput()
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
        $this->assertSame('bar', $command->capturedInput->getArgument('foo'));
    }

    public function testInvokePassesMultiWordArgumentVerbatim()
    {
        $command = new class('proxy:query') extends Command {
            protected function configure(): void
            {
                $this->addArgument('query', InputArgument::OPTIONAL);
            }

            protected function execute(InputInterface $input, OutputInterface $output): int
            {
                $output->writeln((string) $input->getArgument('query'));

                return 0;
            }
        };

        $application = new Application();
        $application->setAutoExit(false);
        $application->add($command);

        $handler = new CommandToolHandler($application, 'proxy:query');
        $result = $handler("SELECT sku FROM catalog_product_entity WHERE sku = 'ABC-123' LIMIT 5;");

        $this->assertSame(
            "SELECT sku FROM catalog_product_entity WHERE sku = 'ABC-123' LIMIT 5;",
            $result
        );
    }

    public function testInvokeParsesLeadingOptionsBeforeArgument()
    {
        $command = new class('proxy:query') extends Command {
            public $capturedInput;

            protected function configure(): void
            {
                $this
                    ->addArgument('query', InputArgument::OPTIONAL)
                    ->addOption('format', null, InputOption::VALUE_OPTIONAL);
            }

            protected function execute(InputInterface $input, OutputInterface $output): int
            {
                $this->capturedInput = $input;
                $output->writeln((string) $input->getArgument('query'));

                return 0;
            }
        };

        $application = new Application();
        $application->setAutoExit(false);
        $application->add($command);

        $handler = new CommandToolHandler($application, 'proxy:query');
        $handler("--format=csv SELECT sku FROM catalog_product_entity LIMIT 5;");

        $this->assertSame('csv', $command->capturedInput->getOption('format'));
        $this->assertSame(
            'SELECT sku FROM catalog_product_entity LIMIT 5;',
            $command->capturedInput->getArgument('query')
        );
    }

    public function testInvokeSplitsWordsForArrayArgument()
    {
        $command = new class('proxy:array') extends Command {
            public $capturedInput;

            protected function configure(): void
            {
                $this->addArgument('type', InputArgument::IS_ARRAY | InputArgument::OPTIONAL);
            }

            protected function execute(InputInterface $input, OutputInterface $output): int
            {
                $this->capturedInput = $input;

                return 0;
            }
        };

        $application = new Application();
        $application->setAutoExit(false);
        $application->add($command);

        $handler = new CommandToolHandler($application, 'proxy:array');
        $handler('config layout');

        $this->assertSame(['config', 'layout'], $command->capturedInput->getArgument('type'));
    }

    public function testInvokePassesCommandNameToInput()
    {
        $command = new class('proxy:command') extends Command {
            /** @var InputInterface|null */
            public $capturedInput;

            protected function configure(): void
            {
                $this->addArgument('foo', InputArgument::OPTIONAL)
                     ->addOption('format', null, InputOption::VALUE_OPTIONAL);
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
        $handler('--format=csv bar');

        $this->assertNotNull($command->capturedInput);
        $this->assertSame(
            "'proxy:command' --format=csv --no-interaction bar",
            (string) $command->capturedInput
        );
    }

    public function testInvokeRendersBooleanFlagsWithoutAValue()
    {
        $command = new class('proxy:flag') extends Command {
            public $capturedInput;

            protected function configure(): void
            {
                $this->addOption('enabled', null, InputOption::VALUE_NONE);
            }

            protected function execute(InputInterface $input, OutputInterface $output): int
            {
                $this->capturedInput = $input;

                return 0;
            }
        };

        $application = new Application();
        $application->setAutoExit(false);
        $application->add($command);

        $handler = new CommandToolHandler($application, 'proxy:flag');
        $handler('--enabled');

        $this->assertNotFalse($command->capturedInput->getOption('enabled'));
        $this->assertSame(
            "'proxy:flag' --enabled --no-interaction",
            (string) $command->capturedInput
        );
    }

    public function testInvokeThrowsWhenCommandAcceptsNoArgumentsButExtraTextGiven()
    {
        $command = new class('proxy:noargs') extends Command {
            protected function execute(InputInterface $input, OutputInterface $output): int
            {
                return 0;
            }
        };

        $application = new Application();
        $application->setAutoExit(false);
        $application->add($command);

        $handler = new CommandToolHandler($application, 'proxy:noargs');

        $this->expectException(ToolCallException::class);
        $handler('unexpected extra text');
    }
}
