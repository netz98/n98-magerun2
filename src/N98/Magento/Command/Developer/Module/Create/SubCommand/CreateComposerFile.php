<?php

namespace N98\Magento\Command\Developer\Module\Create\SubCommand;

use N98\Magento\Command\SubCommand\AbstractSubCommand;

class CreateComposerFile extends AbstractSubCommand
{
    /**
     * @return bool
     */
    public function execute()
    {
        if ($this->config->getBool('isModmanMode')) {
            $outFile = $this->config->getString('modmanRootFolder') . '/composer.json';
        } else {
            $outFile = $this->config->getString('moduleDirectory') . '/composer.json';
        }

        \file_put_contents(
            $outFile,
            $this->getCommand()->getHelper('twig')->render(
                'dev/module/create/composer.json.twig',
                $this->config->getArray('twigVars')
            )
        );

        $this->output->writeln('<info>Created file: <comment>' . $outFile . '<comment></info>');

        return true;
    }
}
