<?php

namespace N98\Magento\Command\System\Setup;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ChangeVersionCommand extends AbstractSetupCommand
{
    /**
     * Setup
     */
    protected function configure()
    {
        $this
            ->setName('sys:setup:change-version')
            ->addArgument('module', InputArgument::REQUIRED, 'Module name')
            ->addArgument('version', InputArgument::REQUIRED, 'New version value')
            ->setDescription('Change module resource version');
        $help = <<<HELP
Change a module's resource version
HELP;
        $this->setHelp($help);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectMagento($output, true);

        if (!$this->initMagento()) {
            return;
        }

        $moduleVersion = $input->getArgument('version');
        $moduleName    = $this->getMagentoModuleName($input->getArgument('module'));

        /** @var \Magento\Framework\Module\ResourceInterface $resource */
        $resource = $this->getMagentoModuleResource();

        $originalVersion = $resource->getDbVersion($moduleName);

        $resource->setDbVersion($moduleName, $moduleVersion);
        $resource->setDataVersion($moduleName, $moduleVersion);

        $output->writeln(
            sprintf(
                '<info>Successfully updated: "%s" from version "%s" to version: "%s"</info>',
                $moduleName,
                $originalVersion,
                $moduleVersion
            )
        );
    }
}
