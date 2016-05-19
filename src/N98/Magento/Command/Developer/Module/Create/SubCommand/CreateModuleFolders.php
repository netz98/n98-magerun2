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
        $config = $this->config;

        if ($config->getBool('isModmanMode')) {
            $modManDir = sprintf('%s_%s/src', $config->getString('vendorNamespace'), $config->getString('moduleName'));
            if (file_exists($modManDir)) {
                throw new \RuntimeException('Module already exists. Stop.');
            }
            mkdir($modManDir, 0777, true);
            $config->setString('magentoRootFolder', './' . $modManDir);
            $config->setString('modmanRootFolder', './' . substr($modManDir, 0, -4));
        }

        $moduleDir = $config->getString('magentoRootFolder')
            . '/app/code'
            . '/' . $config->getString('vendorNamespace')
            . '/' . $config->getString('moduleName');
        if (file_exists($moduleDir)) {
            throw new \RuntimeException('Module already exists. Stop.');
        }

        $config->setString('moduleDirectory', $moduleDir);

        mkdir($moduleDir, 0777, true);
        $this->output->writeln('<info>Created directory: <comment>' . $moduleDir . '<comment></info>');

        // Add etc folder
        mkdir($moduleDir . '/etc');
        $this->output->writeln('<info>Created directory: <comment>' . $moduleDir . '/etc<comment></info>');

        // Add blocks folder
        if ($config->getBool('shouldAddBlocks')) {
            mkdir($moduleDir . '/Block');
            $this->output->writeln('<info>Created directory: <comment>' . $moduleDir . '/Block' . '<comment></info>');
        }

        // Add helpers folder
        if ($config->getBool('shouldAddHelpers')) {
            mkdir($moduleDir . '/Helper');
            $this->output->writeln('<info>Created directory: <comment>' . $moduleDir . '/Helper' . '<comment></info>');
        }

        // Add models folder
        if ($config->getBool('shouldAddModels')) {
            mkdir($moduleDir . '/Model');
            $this->output->writeln('<info>Created directory: <comment>' . $moduleDir . '/Model' . '<comment></info>');
        }

        // Create SQL and Data folder
        if ($config->getBool('shouldAddSetup')) {
            $setupFolder = $moduleDir . '/Setup/';
            mkdir($setupFolder, 0777, true);
            $this->output->writeln('<info>Created directory: <comment>' . $setupFolder . '<comment></info>');
        }

        return true;
    }
}
