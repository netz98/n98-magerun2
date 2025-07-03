<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Script\Repository;

use N98\Magento\Application;
use N98\Magento\Command\AbstractMagentoCommand;

/**
 * Class AbstractRepositoryCommand
 * @package N98\Magento\Command\Script\Repository
 */
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
        $excludedFolders = $config['script']['excluded-folders'];

        $baseName = $application::APP_NAME;
        $magentoRootFolder = $application->getMagentoRootFolder();

        $loader = new ScriptLoader(
            $configScriptFolders,
            $excludedFolders,
            $baseName,
            $magentoRootFolder
        );

        return $loader->getFiles();
    }
}
