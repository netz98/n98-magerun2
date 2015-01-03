<?php

namespace N98\Magento\Command\System\Setup;

use N98\Magento\Command\AbstractMagentoCommand;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RunCommand extends AbstractMagentoCommand
{
    protected function configure()
    {
        $this
            ->setName('sys:setup:run')
            ->setDescription('Runs all new setup scripts.');
        $help = <<<HELP
Runs all setup scripts (no need to call frontend).
This command is useful if you update your system with enabled maintenance mode.
HELP;
        $this->setHelp($help);
    }

    /**
     * @param InputInterface   $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getApplication()->setAutoExit(false);
        $this->detectMagento($output);
        if ($this->initMagento()) {
            try {

                $factory = $this->createSubCommandFactory(
                    $input,
                    $output,
                    'N98\Magento\Command\System\Setup\SubCommand'
                );

                $factory->create('InitZendApp')->execute();

                $this->writeSection($output, 'Schema creation/updates');
                $factory->create('SchemaUpdate')->execute();

                $this->writeSection($output, 'Schema post-updates');
                $factory->create('SchemaUpdate')->execute();

                $this->writeSection($output, 'Installing data');
                $factory->create('DataUpdate')->execute();

                $output->writeln('<info>done</info>');
            } catch (Exception $e) {
                $output->writeln('<error>' . $e->getMessage() . '</error>');
                return 1;
            }
        }

        return 0;
    }

    /**
     * @param OutputInterface $output
     * @param array $moduleNames
     * @param \Magento\Framework\Module\Updater\SetupFactory $setupFactory
     * @param \Magento\Setup\Model\LoggerInterface $logger
     */
    protected function runDataUpdates(OutputInterface $output, $moduleNames, $setupFactory, $logger)
    {

    }
}
