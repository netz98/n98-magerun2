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

    /**
     * @dataProvider multipleArgumentsProvider
     */
    public function testInvokeBindsMultipleArguments(string $arguments, array $expected)
    {
        $command = new class('proxy:customer') extends Command {
            public $capturedInput;

            protected function configure(): void
            {
                $this->addArgument('email', InputArgument::REQUIRED)
                    ->addArgument('password', InputArgument::OPTIONAL)
                    ->addArgument('website', InputArgument::OPTIONAL, '', '1')
                    ->addOption('enabled', null, InputOption::VALUE_NONE);
            }

            protected function execute(InputInterface $input, OutputInterface $output): int
            {
                $this->capturedInput = $input;

                return 0;
            }
        };
        $application = new Application();
        $application->add($command);
        $handler = new CommandToolHandler($application, 'proxy:customer');

        for ($run = 0; $run < 2; $run++) {
            $handler('--enabled ' . $arguments);
            $this->assertSame($expected, [
                $command->capturedInput->getArgument('email'),
                $command->capturedInput->getArgument('password'),
                $command->capturedInput->getArgument('website'),
            ]);
            $this->assertTrue($command->capturedInput->getOption('enabled'));
        }
    }

    public static function multipleArgumentsProvider(): array
    {
        return [
            ['user@example.com secret 2', ['user@example.com', 'secret', '2']],
            ['user@example.com "secret with spaces" 2', ['user@example.com', 'secret with spaces', '2']],
            ["user@example.com '' 2", ['user@example.com', '', '2']],
            ['user@example.com', ['user@example.com', null, '1']],
        ];
    }

    public function testInvokeBindsTrailingArrayArgument()
    {
        $command = new class('proxy:array') extends Command {
            public $capturedInput;

            protected function configure(): void
            {
                $this->addArgument('name', InputArgument::REQUIRED)
                    ->addArgument('values', InputArgument::IS_ARRAY);
            }

            protected function execute(InputInterface $input, OutputInterface $output): int
            {
                $this->capturedInput = $input;

                return 0;
            }
        };
        $application = new Application();
        $application->add($command);
        $handler = new CommandToolHandler($application, 'proxy:array');
        $handler('name first "second value"');

        $this->assertSame('name', $command->capturedInput->getArgument('name'));
        $this->assertSame(['first', 'second value'], $command->capturedInput->getArgument('values'));
    }

    public function testInvokeRejectsExcessPositionalArguments()
    {
        $command = new Command('proxy:customer');
        $command->addArgument('email')->addArgument('website');
        $application = new Application();
        $application->add($command);
        $handler = new CommandToolHandler($application, 'proxy:customer');

        $this->expectException(ToolCallException::class);
        $this->expectExceptionMessage('too many positional arguments');
        $handler('user@example.com 1 unexpected');
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
        $this->assertFalse($command->capturedInput->isInteractive());
        $this->assertSame(
            "'proxy:command' --format=csv bar",
            (string) $command->capturedInput
        );
    }

    public function testInvokeBindsBooleanFlagsAsTrue()
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

        // Regression test for https://github.com/netz98/n98-magerun2/issues/2132: a VALUE_NONE
        // flag must bind as the boolean `true`, not the empty string, so that command code
        // checking it with a plain truthy test (`if ($input->getOption('enabled'))`) works.
        $this->assertTrue($command->capturedInput->getOption('enabled'));
        $this->assertSame(
            "'proxy:flag' --enabled",
            (string) $command->capturedInput
        );
    }

    public function testInvokeBindsArgumentCorrectlyOnRepeatedCalls()
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

        $firstResult = $handler('bar');
        $this->assertSame('bar', $firstResult);
        $this->assertSame('bar', $command->capturedInput->getArgument('foo'));

        $secondResult = $handler('baz');
        $this->assertSame('baz', $secondResult);
        $this->assertSame('baz', $command->capturedInput->getArgument('foo'));
    }

    public function testInvokeBindsQueryArgumentOnRepeatedCalls()
    {
        $command = new class('proxy:query') extends Command {
            /** @var InputInterface|null */
            public $capturedInput;

            protected function configure(): void
            {
                $this->addArgument('query', InputArgument::OPTIONAL);
            }

            protected function execute(InputInterface $input, OutputInterface $output): int
            {
                $this->capturedInput = $input;

                if ($input->getArgument('query') === null) {
                    throw new \RuntimeException('No SQL query provided. Please pass the query as a command argument.');
                }

                $output->writeln((string) $input->getArgument('query'));

                return 0;
            }
        };

        $application = new Application();
        $application->setAutoExit(false);
        $application->add($command);

        $handler = new CommandToolHandler($application, 'proxy:query');

        $this->assertSame('SELECT 1', $handler('SELECT 1'));
        $this->assertSame(
            'SELECT store_id, code, name FROM store LIMIT 5',
            $handler('SELECT store_id, code, name FROM store LIMIT 5')
        );
        $this->assertSame(
            'SELECT COUNT(*) FROM catalog_product_entity',
            $handler('SELECT COUNT(*) FROM catalog_product_entity')
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
