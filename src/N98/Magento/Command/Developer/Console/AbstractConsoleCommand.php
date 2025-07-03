<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Developer\Console;

use Magento\Framework\App\ObjectManager;
use Psy\Command\ReflectingCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class AbstractConsoleCommand
 * @package N98\Magento\Command\Developer\Console
 */
abstract class AbstractConsoleCommand extends ReflectingCommand
{
    /**
     * @param string $variable
     * @param mixed $value
     *
     * @return void
     */
    public function setScopeVariable($variable, $value)
    {
        $variables = $this->context->getAll();
        $variables[$variable] = $value;

        $this->context->setAll($variables);
    }

    /**
     * @param string $type
     * @return mixed
     */
    public function get($type)
    {
        $di = $this->getScopeVariable('di');

        /** @var $di ObjectManager */
        return $di->get($type);
    }

    /**
     * @param string $type
     * @param array $arguments
     * @return mixed
     */
    public function create($type, $arguments = [])
    {
        $di = $this->getScopeVariable('di');

        /** @var $di ObjectManager */
        return $di->create($type, $arguments);
    }

    /**
     * Call n98-magerun command
     *
     * @param string $commandName
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws \Exception
     */
    public function callMagerunCommand($commandName, InputInterface $input, OutputInterface $output)
    {
        $commandName = rtrim($commandName, ';');
        /** @var \N98\Magento\Command\AbstractMagentoCommand $command */
        $command = $this->getScopeVariable('magerun')->find($commandName);

        return $command->run($input, $output);
    }

    /**
     * Call psy console command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws \Exception
     */
    public function callCommand(InputInterface $input, OutputInterface $output)
    {
        if ($input->hasArgument('command')) {
            $commandName = $input->getArgument('command');
        } else {
            $commandName = $input->getFirstArgument();
        }

        $command = $this->getApplication()->find($commandName);

        return $command->run($input, $output);
    }

    /**
     * @return \N98\Magento\Application
     */
    public function getMagerunApplication()
    {
        return $this->getScopeVariable('magerun');
    }

    /**
     * @return \Magento\Framework\App\ProductMetadataInterface
     */
    public function getMagentoVersion()
    {
        return $this->getScopeVariable('magentoVersion');
    }
}
