<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Developer\Module\Create\SubCommand;

use N98\Magento\Command\SubCommand\AbstractSubCommand;
use Symfony\Component\Console\Input\ArrayInput;

/**
 * Class EnableModule
 * @package N98\Magento\Command\Developer\Module\Create\SubCommand
 */
class EnableModule extends AbstractSubCommand
{
    /**
     * @return void
     * @throws \Exception
     */
    public function execute()
    {
        if (!$this->config->getBool('shouldEnableModule')) {
            return;
        }

        if ($this->config->getBool('isModmanMode')) {
            $this->output->writeln('<error>Module cannot be activated in modman mode</error>');
            return;
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
