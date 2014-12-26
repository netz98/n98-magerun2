<?php

namespace N98\Magento\Command\Installer\SubCommand;

use N98\Magento\Command\SubCommand\AbstractSubCommand;

class PreCheckPhp extends AbstractSubCommand
{
    /**
     * Check PHP environment against minimal required settings modules
     *
     * @return bool
     */
    public function execute()
    {
        $extensions = $this->commandConfig['installation']['pre-check']['php']['extensions'];
        $missingExtensions = array();
        foreach ($extensions as $extension) {
            if (!extension_loaded($extension)) {
                $missingExtensions[] = $extension;
            }
        }

        if (count($missingExtensions) > 0) {
            throw new \RuntimeException(
                'The following PHP extensions are required to start installation: ' . implode(',', $missingExtensions)
            );
        }
    }
}