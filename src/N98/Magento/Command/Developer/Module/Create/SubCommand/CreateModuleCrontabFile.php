<?php

namespace N98\Magento\Command\Developer\Module\Create\SubCommand;

use N98\Magento\Command\SubCommand\AbstractSubCommand;

/**
 * Class CreateModuleCrontabFile
 * @package N98\Magento\Command\Developer\Module\Create\SubCommand
 */
class CreateModuleCrontabFile extends AbstractSubCommand
{
    /**
     * @return void
     */
    public function execute()
    {
        $outFile = $this->config->getString('moduleDirectory') . '/etc/crontab.xml';

        \file_put_contents(
            $outFile,
            $this->getCommand()->getHelper('twig')->render(
                'dev/module/create/app/code/module/etc/crontab.xml.twig',
                $this->config->getArray('twigVars')
            )
        );

        $this->output->writeln('<info>Created file: <comment>' . $outFile . '<comment></info>');
    }
}
