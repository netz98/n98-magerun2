<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\SubCommand;

use N98\Magento\Command\AbstractMagentoCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Interface SubCommandInterface
 * @package N98\Magento\Command\SubCommand
 */
interface SubCommandInterface
{
    /**
     * @param ConfigBag $config
     * @return void
     */
    public function setConfig(ConfigBag $config);

    /**
     * @param array $commandConfig
     * @return void
     */
    public function setCommandConfig(array $commandConfig);

    /**
     * @param InputInterface $input
     * @return void
     */
    public function setInput(InputInterface $input);

    /**
     * @param OutputInterface $output
     * @return void
     */
    public function setOutput(OutputInterface $output);

    /**
     * @return AbstractMagentoCommand
     */
    public function getCommand();

    /**
     * @param AbstractMagentoCommand $command
     * @return void
     */
    public function setCommand(AbstractMagentoCommand $command);

    /**
     * @return void
     */
    public function execute();
}
