<?php

namespace N98\Magento\Command\Installer\SubCommand;

use N98\Magento\Command\SubCommand\AbstractSubCommand;
use Symfony\Component\Finder\Finder;

class SetDirectoryPermissions extends AbstractSubCommand
{
    /**
     * @return bool
     */
    public function execute()
    {
        try {
            $installationFolder = $this->config->getString('installationFolder');
            
            $varFolder = $installationFolder . DIRECTORY_SEPARATOR . 'var';
            if (!is_dir($varFolder)) {
                @mkdir($varFolder);
            }
            @chmod($varFolder, 0777);

            $varCacheFolder = $installationFolder . DIRECTORY_SEPARATOR . 'var/cache';
            if (!is_dir($varCacheFolder)) {
                @mkdir($varCacheFolder);
            }
            @chmod($varCacheFolder, 0777);

            $mediaFolder = $installationFolder
                         . DIRECTORY_SEPARATOR
                         . 'pub'
                         . DIRECTORY_SEPARATOR
                         . 'media';
            if (!is_dir($mediaFolder)) {
                @mkdir($mediaFolder);
            }
            @chmod($mediaFolder, 0777);

            $finder = Finder::create();
            $finder->directories()
                ->ignoreUnreadableDirs(true)
                ->in(array($varFolder, $mediaFolder));
            foreach ($finder as $dir) {
                @chmod($dir->getRealpath(), 0777);
            }
        } catch (\Exception $e) {
            $this->output->writeln('<error>' . $e->getMessage() . '</error>');
        }
    }
}
