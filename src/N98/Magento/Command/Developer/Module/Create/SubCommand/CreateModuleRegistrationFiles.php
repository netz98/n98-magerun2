<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Developer\Module\Create\SubCommand;

use N98\Magento\Command\SubCommand\AbstractSubCommand;

/**
 * Class CreateModuleRegistrationFiles
 * @package N98\Magento\Command\Developer\Module\Create\SubCommand
 */
class CreateModuleRegistrationFiles extends AbstractSubCommand
{
    /**
     * @return void
     */
    public function execute()
    {
        $this->createRegistrationFile();
        $this->createEtcModuleFile();
    }

    protected function createEtcModuleFile()
    {
        $outFile = $this->config->getString('moduleDirectory') . '/etc/module.xml';

        \file_put_contents(
            $outFile,
            $this->getCommand()->getHelper('twig')->render(
                'dev/module/create/app/code/module/etc/module.xml.twig',
                $this->config->getArray('twigVars')
            )
        );

        $this->output->writeln('<info>Created file: <comment>' . $outFile . '<comment></info>');
    }

    protected function createRegistrationFile()
    {
        $outFile = $this->config->getString('moduleDirectory') . '/registration.php';

        \file_put_contents(
            $outFile,
            $this->getCommand()->getHelper('twig')->render(
                'dev/module/create/app/code/module/registration.php.twig',
                $this->config->getArray('twigVars')
            )
        );

        $this->output->writeln('<info>Created file: <comment>' . $outFile . '<comment></info>');
    }
}
