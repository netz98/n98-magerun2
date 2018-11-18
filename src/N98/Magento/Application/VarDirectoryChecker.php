<?php

namespace N98\Magento\Application;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class VarDirectoryChecker
 * @package N98\Magento\Application
 */
class VarDirectoryChecker
{
    /**
     * @param OutputInterface $output
     * @return null|false
     */
    public function check(OutputInterface $output)
    {
        $tempVarDir = sys_get_temp_dir() . '/magento/var';
        if ((!OutputInterface::VERBOSITY_NORMAL) <= $output->getVerbosity() && !is_dir($tempVarDir)) {
            return true;
        }

        $output->writeln([
            sprintf('<warning>Cache fallback folder %s was found.</warning>', $tempVarDir),
            '',
            'n98-magerun2 is using the fallback folder. If there is another folder configured for Magento, this ' .
            'can cause serious problems.',
            'Please refer to https://github.com/netz98/n98-magerun/wiki/File-system-permissions ' .
            'for more information.',
            '',
        ]);

        return false;
    }
}
