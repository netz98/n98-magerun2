<?php

namespace N98\Magento\Command\Developer\Module\Create\SubCommand;

use N98\Magento\Command\SubCommand\AbstractSubCommand;

class CreateModuleRegistrationFile extends AbstractSubCommand
{
    /**
     * @return bool
     */
    public function execute()
    {
        $outFile = $this->config->getString('moduleDirectory') . '/etc/module.xml';

        \file_put_contents(
            $outFile,
            $this->getCommand()->getHelper('twig')->render(
                'dev/module/create/app/code/module/etc/module.xml.twig',
                $this->config->getArray('twigVars')
            )
        );

        $this->output->writeln('<info>Created file: <comment>' .  $outFile .'<comment></info>');
    }
}