<?php

namespace N98\Magento\Command\Cache;

use Exception;
use Magento\Framework\App\Cache\Manager;
use Magento\Framework\App\Cache\StateInterface;
use N98\Magento\Command\AbstractMagentoCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractModifierCommand extends AbstractMagentoCommand
{
    const INVALID_TYPES_MESSAGE = null;
    const ABORT_MESSAGE = null;
    const EXCEPTION_MESSAGE = null;
    const SUCCESS_MESSAGE = null;
    const TARGET_IS_ENABLED = null;

    /**
     * @param bool $isEnabled
     *
     * @return array
     * @throws \Exception
     */
    protected function getCacheTypes($isEnabled)
    {
        /** @var $listCommand ListCommand */
        // Run the internal cache:list command to find all cache types which are disabled
        $listCommand = $this->getApplication()->find('cache:list');
        $listCommand->run(new ArrayInput(['command' => 'cache:list', '--enabled' => $isEnabled]), new NullOutput());

        return $listCommand->getTypes();
    }

    /**
     * @return \Magento\Framework\App\Cache\Manager;
     */
    public function getCacheManager()
    {
        return $this->getObjectManager()->get(Manager::class);
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectMagento($output, true);
        $this->initMagento();

        $types = $input->getArgument('type');

        if (empty($types)) {
            // If no argument is supplied, we are modifying all targets
            $types = array_keys($this->getCacheTypes(static::TARGET_IS_ENABLED));
        } else {
            $types = (array) $types;
        }

        // Find out which types simply do not exist or are not affected by modifier
        $invalidTypes = array_diff($types, array_keys($this->getCacheTypes(!static::TARGET_IS_ENABLED)));

        if (!empty($invalidTypes)) {
            $output->writeln(sprintf(static::INVALID_TYPES_MESSAGE, implode(', ', $invalidTypes)));
        }

        // Strip the input against the invalid types
        $types = array_diff($types, $invalidTypes);

        if (empty($types)) {
            $output->writeln(static::ABORT_MESSAGE);

            return Command::FAILURE;
        }

        /** @var $cacheState \Magento\Framework\App\Cache\StateInterface */
        $cacheState = $this->getObjectManager()->get(StateInterface::class);
        $touchedTypes = [];

        try {
            foreach ($types as $type) {
                if ($cacheState->isEnabled($type) == static::TARGET_IS_ENABLED) {
                    continue;
                }

                $cacheState->setEnabled($type, (bool) static::TARGET_IS_ENABLED);
                $touchedTypes[] = $type;
            }

            $cacheState->persist();
        } catch (Exception $e) {
            $output->writeln(sprintf(static::EXCEPTION_MESSAGE, $e->getMessage()));

            return Command::FAILURE;
        }

        $output->writeln(sprintf(static::SUCCESS_MESSAGE, implode(', ', $touchedTypes)));

        return Command::SUCCESS;
    }
}
