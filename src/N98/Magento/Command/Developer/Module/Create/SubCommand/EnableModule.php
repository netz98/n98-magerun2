<?php

namespace N98\Magento\Command\Developer\Module\Create\SubCommand;

use N98\Magento\Command\SubCommand\AbstractSubCommand;
use Symfony\Component\Console\Input\ArrayInput;

class EnableModule extends AbstractSubCommand
{
    /**
     * @return bool
     */
    public function execute()
    {
        if (!$this->config->getBool('shouldEnableModule')) {
            return false;
        }

        if ($this->config->getBool('isModmanMode')) {
            $this->output->writeln('<error>Module cannot be activated in modman mode</error>');
            return false;
        }

        $application = $this->getCommand()->getApplication();

        $combinedModuleName = $this->config->getString('vendorNamespace')
                            . '_'
                            . $this->config->getString('moduleName');

        $application->setAutoExit(false);
        $application->run(
            new ArrayInput(
                [
                    'command' => 'module:enable',
                    'module'  => [$combinedModuleName],
                ]
            )
        );
    }
}
