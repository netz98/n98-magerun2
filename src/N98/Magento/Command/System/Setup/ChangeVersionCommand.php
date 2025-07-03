<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\System\Setup;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ChangeVersionCommand
 * @package N98\Magento\Command\System\Setup
 */
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
     * @return int
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectMagento($output, true);

        if (!$this->initMagento()) {
            return Command::FAILURE;
        }

        $moduleVersion = $input->getArgument('version');
        $moduleName = $this->getMagentoModuleName($input->getArgument('module'));

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

        return Command::SUCCESS;
    }
}
