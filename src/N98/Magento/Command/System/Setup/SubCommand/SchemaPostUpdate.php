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
 * Class SchemaPostUpdate
 * @package N98\Magento\Command\System\Setup\SubCommand
 */
class SchemaPostUpdate extends AbstractSubCommand
{
    /**
     * @return bool
     */
    public function execute()
    {
        $moduleNames = $this->config->getArray('moduleNames');
        $setupFactory = $this->config->getObject('setupFactory');
        $logger = $this->config->getObject('logger');
        $progress = $this->getCommand()->getHelper('progress');

        $progress->start($this->output, count($moduleNames));
        foreach ($moduleNames as $moduleName) {
            if (OutputInterface::VERBOSITY_VERBOSE <= $this->output->getVerbosity()) {
                $this->output->writeln("\n" . '<debug>' . $moduleName . '</debug>');
            }

            $setup = $setupFactory->createSetupModule($logger, $moduleName);
            $setup->applyRecurringUpdates();
            $progress->advance();
        }
        $progress->finish();

        return true;
    }
}
