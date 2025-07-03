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
 * Class CreateSetupFiles
 * @package N98\Magento\Command\Developer\Module\Create\SubCommand
 */
class CreateSetupFiles extends AbstractSubCommand
{
    /**
     * @return void
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
