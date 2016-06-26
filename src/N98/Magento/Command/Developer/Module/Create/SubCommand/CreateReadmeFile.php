<?php

namespace N98\Magento\Command\Developer\Module\Create\SubCommand;

use N98\Magento\Command\SubCommand\AbstractSubCommand;

class CreateReadmeFile extends AbstractSubCommand
{
    /**
     * @see https://raw.github.com/sprankhub/Magento-Extension-Sample-Readme/master/readme.markdown
     *
     * @return bool
     */
    public function execute()
    {
        if ($this->config->getBool('isModmanMode')) {
            $outFile = $this->config->getString('modmanRootFolder') . '/readme.md';
        } else {
            $outFile = $this->config->getString('moduleDirectory') . '/readme.md';
        }


        \file_put_contents(
            $outFile,
            $this->getCommand()->getHelper('twig')->render(
                'dev/module/create/app/code/module/readme.md.twig',
                $this->config->getArray('twigVars')
            )
        );

        $this->output->writeln('<info>Created file: <comment>' . $outFile . '<comment></info>');

        return true;
    }
}
