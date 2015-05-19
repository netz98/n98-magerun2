<?php

namespace N98\Magento\Command\Developer\Module\Create\SubCommand;

use N98\Magento\Command\SubCommand\AbstractSubCommand;

class CreateModuleFolders extends AbstractSubCommand
{
    /**
     * @return bool
     */
    public function execute()
    {
        if ($this->config->getBool('isModmanMode')) {
            $modManDir = $this->config->getString('vendorNamespace') . '_' . $this->config->getString('moduleName'). '/src';
            if (file_exists($modManDir)) {
                throw new \RuntimeException('Module already exists. Stop.');
            }
            mkdir($modManDir, 0777, true);
            $this->config->setString('magentoRootFolder', './' . $modManDir);
            $this->config->setString('modmanRootFolder', './' . substr($modManDir, 0, -4));
        }

        $moduleDir = $this->config->getString('magentoRootFolder')
            . '/app/code'
            . '/' . $this->config->getString('vendorNamespace')
            . '/' . $this->config->getString('moduleName');
        if (file_exists($moduleDir)) {
            throw new \RuntimeException('Module already exists. Stop.');
        }

        $this->config->setString('moduleDirectory', $moduleDir);

        mkdir($moduleDir, 0777, true);
        $this->output->writeln('<info>Created directory: <comment>' .  $moduleDir .'<comment></info>');

        // Add etc folder
        mkdir($moduleDir . '/etc');
        $this->output->writeln('<info>Created directory: <comment>' .  $moduleDir .'/etc<comment></info>');

        // Add blocks folder
        if ($this->config->getBool('shouldAddBlocks')) {
            mkdir($moduleDir . '/Block');
            $this->output->writeln('<info>Created directory: <comment>' .  $moduleDir . '/Block' .'<comment></info>');
        }

        // Add helpers folder
        if ($this->config->getBool('shouldAddHelpers')) {
            mkdir($moduleDir . '/Helper');
            $this->output->writeln('<info>Created directory: <comment>' .  $moduleDir . '/Helper' .'<comment></info>');
        }

        // Add models folder
        if ($this->config->getBool('shouldAddModels')) {
            mkdir($moduleDir . '/Model');
            $this->output->writeln('<info>Created directory: <comment>' .  $moduleDir . '/Model' .'<comment></info>');
        }

        // Create SQL and Data folder
        if ($this->config->getBool('shouldAddSetup')) {
            $setupFolder = $moduleDir . '/Setup/';
            mkdir($setupFolder, 0777, true);
            $this->output->writeln('<info>Created directory: <comment>' . $setupFolder . '<comment></info>');
        }

        return true;
    }
}