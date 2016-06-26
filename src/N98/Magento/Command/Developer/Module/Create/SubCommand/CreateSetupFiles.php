<?php

namespace N98\Magento\Command\Developer\Module\Create\SubCommand;

use N98\Magento\Command\SubCommand\AbstractSubCommand;

class CreateSetupFiles extends AbstractSubCommand
{
    /**
     * @return bool
     */
    public function execute()
    {
        $setupFolder = $this->config->getString('moduleDirectory') . '/Setup';

        \file_put_contents(
            $setupFolder . '/InstallSchema.php',
            $this->getCommand()->getHelper('twig')->render(
                'dev/module/create/app/code/module/Setup/InstallSchema.php.twig',
                $this->config->getArray('twigVars')
            )
        );
        $this->output->writeln(
            '<info>Created file: <comment>' . $setupFolder . '/InstallSchema.php' . '<comment></info>'
        );

        \file_put_contents(
            $setupFolder . '/InstallData.php',
            $this->getCommand()->getHelper('twig')->render(
                'dev/module/create/app/code/module/Setup/InstallData.php.twig',
                $this->config->getArray('twigVars')
            )
        );
        $this->output->writeln(
            '<info>Created file: <comment>' . $setupFolder . '/InstallData.php' . '<comment></info>'
        );

        \file_put_contents(
            $setupFolder . '/UpgradeSchema.php',
            $this->getCommand()->getHelper('twig')->render(
                'dev/module/create/app/code/module/Setup/UpgradeSchema.php.twig',
                $this->config->getArray('twigVars')
            )
        );
        $this->output->writeln(
            '<info>Created file: <comment>' . $setupFolder . '/UpgradeSchema.php' . '<comment></info>'
        );

        \file_put_contents(
            $setupFolder . '/UpgradeData.php',
            $this->getCommand()->getHelper('twig')->render(
                'dev/module/create/app/code/module/Setup/UpgradeData.php.twig',
                $this->config->getArray('twigVars')
            )
        );
        $this->output->writeln(
            '<info>Created file: <comment>' . $setupFolder . '/UpgradeData.php' . '<comment></info>'
        );
    }
}
