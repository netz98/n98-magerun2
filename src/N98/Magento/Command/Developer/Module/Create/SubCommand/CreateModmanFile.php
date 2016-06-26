<?php

namespace N98\Magento\Command\Developer\Module\Create\SubCommand;

use N98\Magento\Command\SubCommand\AbstractSubCommand;

class CreateModmanFile extends AbstractSubCommand
{
    /**
     * @return bool
     */
    public function execute()
    {
        $outFile = $this->config->getString('modmanRootFolder') . '/modman';

        \file_put_contents(
            $outFile,
            $this->getCommand()->getHelper('twig')->render(
                'dev/module/create/modman.twig',
                $this->config->getArray('twigVars')
            )
        );

        $this->output->writeln('<info>Created file: <comment>' . $outFile . '<comment></info>');

        return true;
    }
}
