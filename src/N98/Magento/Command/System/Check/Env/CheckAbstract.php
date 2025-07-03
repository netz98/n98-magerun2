<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\System\Check\Env;

use Dflydev\DotAccessData\Data;
use N98\Magento\Command\CommandAware;
use N98\Magento\Command\CommandConfigAware;
use N98\Magento\Command\System\Check\ResultCollection;
use N98\Magento\Command\System\Check\SimpleCheck;
use N98\Magento\Command\System\CheckCommand;
use Symfony\Component\Console\Command\Command;

abstract class CheckAbstract implements SimpleCheck, CommandAware, CommandConfigAware
{
    /**
     * @var ResultCollection
     */
    protected $_results;

    /**
     * @var array
     */
    protected $_commandConfig;

    /**
     * @var CheckCommand
     */
    protected $_checkCommand;

    /**
     * @var \Dflydev\DotAccessData\Data
     */
    protected $_dot;

    /**
     * @var array
     */
    protected $_env;

    /**
     * @var string
     */
    protected $_envFilePath;

    /**
     * @param ResultCollection $results
     */
    public function check(ResultCollection $results)
    {
        $this->_results = $results;
        $this->_envFilePath = $this->_checkCommand->getApplication()->getMagentoRootFolder() . '/app/etc/env.php';

        $envArray = include $this->_envFilePath;
        $this->_env = $envArray;
        $this->_dot = new Data($envArray);

        $this->checkEnv();
    }

    public function checkEnv()
    {
        // override in sub-class
    }

    /**
     * @param array $commandConfig
     */
    public function setCommandConfig(array $commandConfig)
    {
        $this->_commandConfig = $commandConfig;
    }

    /**
     * @param Command $command
     */
    public function setCommand(Command $command)
    {
        $this->_checkCommand = $command;
    }
}
