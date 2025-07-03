<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Developer\Module\Create\SubCommand;

use N98\Magento\Command\SubCommand\AbstractSubCommand;

/**
 * Class CreateComposerFile
 * @package N98\Magento\Command\Developer\Module\Create\SubCommand
 */
class CreateComposerFile extends AbstractSubCommand
{
    /**
     * @return void
     */
    public function execute()
    {
        if ($this->config->getBool('isModmanMode')) {
            $outFile = $this->config->getString('modmanRootFolder') . '/composer.json';
        } else {
            $outFile = $this->config->getString('moduleDirectory') . '/composer.json';
        }

        \file_put_contents(
            $outFile,
            $this->getCommand()->getHelper('twig')->render(
                'dev/module/create/composer.json.twig',
                $this->config->getArray('twigVars')
            )
        );

        $this->output->writeln('<info>Created file: <comment>' . $outFile . '<comment></info>');
    }
}
