<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Installer\SubCommand;

use N98\Magento\Command\SubCommand\AbstractSubCommand;
use N98\Util\Filesystem;
use Symfony\Component\Finder\Finder;

/**
 * Class RemoveEmptyFolders
 * @package N98\Magento\Command\Installer\SubCommand
 */
class RemoveEmptyFolders extends AbstractSubCommand
{
    /**
     * @return void
     */
    public function execute()
    {
        if (is_dir(getcwd() . '/vendor')) {
            $finder = new Finder();
            $finder->files()->depth(3)->in(getcwd() . '/vendor');
            if ($finder->count() == 0) {
                $filesystem = new Filesystem();
                $filesystem->recursiveRemoveDirectory(getcwd() . '/vendor');
            }
        }
    }
}
