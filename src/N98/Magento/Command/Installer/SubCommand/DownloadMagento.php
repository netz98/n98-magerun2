<?php

namespace N98\Magento\Command\Installer\SubCommand;

use N98\Magento\Command\SubCommand\AbstractSubCommand;

class DownloadMagento extends AbstractSubCommand
{
    /**
     * @return bool
     */
    public function execute()
    {
        if ($this->input->getOption('noDownload')) {
            return false;
        }

        try {
            $package = $this->getCommand()->createComposerPackageByConfig($this->config['magentoVersionData']);
            $this->config->setObject('magentoPackage', $package);

            if (file_exists($this->config->getString('installationFolder') . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Mage.php')) {
                $this->output->writeln('<error>A magento installation already exists in this folder </error>');
                return false;
            }

            $composer = $this->getCommand()->getComposer($this->input, $this->output);
            $targetFolder = $this->getTargetFolderByType($composer, $package, $this->config->getString('installationFolder'));
            $this->config->setObject(
                'magentoPackage',
                $this->getCommand()->downloadByComposerConfig(
                    $this->input,
                    $this->output,
                    $package,
                    $targetFolder,
                    true
                )
            );

            if ($this->getCommand()->isSourceTypeRepository($package->getSourceType())) {
                $filesystem = new \N98\Util\Filesystem;
                $filesystem->recursiveCopy($targetFolder, $this->config['installationFolder'], array('.git', '.hg'));
            } else {
                $filesystem = new \Composer\Util\Filesystem();
                $filesystem->copyThenRemove(
                    $this->config['installationFolder'] . '/_n98_magerun_download', $this->config['installationFolder']
                );
            }

        } catch (\Exception $e) {
            $this->output->writeln('<error>' . $e->getMessage() . '</error>');
            return false;
        }

        return true;
    }

    /**
     * construct a folder to where magerun will download the source to,
     * cache git/hg repositories under COMPOSER_HOME
     *
     * @param $composer
     * @param $package
     * @param $installationFolder
     *
     * @return string
     */
    protected function getTargetFolderByType($composer, $package, $installationFolder)
    {
        $type = $package->getSourceType();
        if ($this->getCommand()->isSourceTypeRepository($type)) {
            $targetPath = sprintf(
                '%s/%s/%s/%s',
                $composer->getConfig()->get('cache-dir'),
                '_n98_magerun_download',
                $type,
                preg_replace('{[^a-z0-9.]}i', '-', $package->getSourceUrl())
            );
        } else {
            $targetPath = sprintf(
                '%s/%s',
                $installationFolder,
                '_n98_magerun_download'
            );
        }

        return $targetPath;
    }
}