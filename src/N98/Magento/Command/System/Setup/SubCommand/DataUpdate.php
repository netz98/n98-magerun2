<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\System\Setup\SubCommand;

use N98\Magento\Command\SubCommand\AbstractSubCommand;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class DataUpdate
 * @package N98\Magento\Command\System\Setup\SubCommand
 */
class DataUpdate extends AbstractSubCommand
{
    /**
     * @return bool
     */
    public function execute()
    {
        $moduleNames = $this->config->getArray('moduleNames');
        $setupFactory = $this->getCommand()
            ->getApplication()
            ->getObjectManager()
            ->get('Magento\Framework\Module\Updater\SetupFactory');

        $progress = $this->getCommand()->getHelper('progress');

        $resourceResolver = $this->getCommand()
            ->getApplication()
            ->getObjectManager()
            ->get('Magento\Framework\Module\ResourceInterface');
        /* @var $resourceResolver \Magento\Framework\Module\ResourceInterface */

        $dbVersionInfo = $this->getCommand()
            ->getApplication()
            ->getObjectManager()
            ->get('Magento\Framework\Module\DbVersionInfo');
        /* @var $dbVersionInfo \Magento\Framework\Module\DbVersionInfo */

        $progress->start($this->output, count($moduleNames));
        foreach ($moduleNames as $moduleName) {
            if (OutputInterface::VERBOSITY_VERBOSE <= $this->output->getVerbosity()) {
                $this->output->writeln("\n" . '<debug>' . $moduleName . '</debug>');
            }

            foreach ($resourceResolver->getResourceList($moduleName) as $resourceName) {
                if (OutputInterface::VERBOSITY_VERBOSE <= $this->output->getVerbosity()) {
                    $this->output->writeln("\n" . '<debug>' . $moduleName . '</debug>');
                }

                if (!$dbVersionInfo->isDataUpToDate($moduleName)) {
                    if (OutputInterface::VERBOSITY_VERBOSE <= $this->output->getVerbosity()) {
                        $this->output->writeln("\n" . '<debug>Run ' . $resourceName . '</debug>');
                    }

                    $setupFactory->create($resourceName, $moduleName)->applyDataUpdates();
                }
            }
            $progress->advance();
        }
        $progress->finish();

        return true;
    }
}
