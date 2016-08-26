<?php

namespace N98\Magento\Command\Script\Repository;

use N98\Magento\Application;
use N98\Magento\Command\AbstractMagentoCommand;

class AbstractRepositoryCommand extends AbstractMagentoCommand
{
    /**
     * Extension of n98-magerun scripts
     */
    const MAGERUN_EXTENSION = '.magerun';

    /**
     * @return array
     */
    protected function getScripts()
    {
        /** @var $application Application */
        $application = $this->getApplication();

        $config = $application->getConfig();
        $configScriptFolders = $config['script']['folders'];
        $baseName = $application::APP_NAME;
        $magentoRootFolder = $application->getMagentoRootFolder();

        $loader = new ScriptLoader($configScriptFolders, $baseName, $magentoRootFolder);
        $files = $loader->getFiles();

        return $files;
    }
}
