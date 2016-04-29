<?php

namespace N98\Magento\Command\Installer\SubCommand;

use N98\Magento\Command\SubCommand\AbstractSubCommand;

class RewriteHtaccessFile extends AbstractSubCommand
{
    /**
     * @return bool
     */
    public function execute()
    {
        if (
            $this->input->getOption('useDefaultConfigParams') !== null
            || $this->input->getOption('replaceHtaccessFile') === null
        ) {
            return;
        }

        $this->getCommand()->getApplication()->setAutoExit(false);

        $dialog = $this->getCommand()->getHelper('dialog');

        if ($this->input->getOption('replaceHtaccessFile') !== null) {
            $replaceHtaccessFile = $this->getCommand()->parseBoolOption($this->input->getOption('replaceHtaccessFile'));
        } else {
            $replaceHtaccessFile = $dialog->askConfirmation(
                $this->output,
                '<question>Write BaseURL to .htaccess file?</question> <comment>[n]</comment>: ',
                false
            );
        }

        if ($replaceHtaccessFile) {
            $this->replaceHtaccessFile();
        }
    }

    protected function replaceHtaccessFile()
    {
        $installationArgs = $this->config->getArray('installation_args');
        $baseUrl = $installationArgs['base-url'];
        $htaccessFile = $this->config->getString('installationFolder') . '/pub/.htaccess';

        $this->_backupOriginalFile($htaccessFile);
        $this->_replaceContent($htaccessFile, $baseUrl);
    }

    protected function _backupOriginalFile($htaccesFile)
    {
        copy(
            $htaccesFile,
            $htaccesFile . '.dist'
        );
    }

    /**
     * @param string $htaccessFile
     * @param string $baseUrl
     */
    protected function _replaceContent($htaccessFile, $baseUrl)
    {
        $content = file_get_contents($htaccessFile);
        $content = str_replace('#RewriteBase /magento/', 'RewriteBase ' . parse_url($baseUrl, PHP_URL_PATH), $content);
        file_put_contents($htaccessFile, $content);
    }
}
