<?php
namespace N98\Magento\Command\Cache;

use Magento\Framework\App\Cache\Type\ConfigSegment;
use N98\Magento\Command\AbstractMagentoCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use N98\Util\Console\Helper\Table\Renderer\RendererFactory;

class EnableCommand extends AbstractMagentoCommand
{
    const INVALID_TYPES_MESSAGE = '<error>The following cache types do not exist or are already enabled: %s</error>';

    const ABORT_MESSAGE = '<info>Nothing to do!</info>';

    const EXCEPTION_MESSAGE = '<error>Something went wrong: %s</error>';

    const SUCCESS_MESSAGE = '<info>The following cache types were enabled: %s</info>';

    protected function configure()
    {
        $this
            ->setName('cache:enable')
            ->setDescription('Enables Magento caches')
            ->addArgument(
                'type',
                InputArgument::IS_ARRAY,
                'Type of cache to enable (separate multiple types with a space)'
            )
            ->addOption(
                'format',
                null,
                InputOption::VALUE_OPTIONAL,
                'Output Format. One of [' . implode(',', RendererFactory::getFormats()) . ']'
            )
        ;
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectMagento($output, true);
        $this->initMagento();

        $types = (array) $input->getArgument('type');

        /** @var $listCommand ListCommand */
        // Run the internal cache:list command to find all cache types which are disabled
        $listCommand = $this->getApplication()->find('cache:list');
        $listCommand->run(new ArrayInput(['command' => 'cache:list', '--enabled' => 0]), new NullOutput());

        // Find out which types simply do not exist or already enabled
        $invalidTypes = array_diff($types, array_keys($listCommand->getTypes()));

        if (! empty($invalidTypes)) {
            $output->writeln(sprintf(self::INVALID_TYPES_MESSAGE, implode(', ', $invalidTypes)));
        }

        // Strip the input against the invalid types
        $types = array_diff($types, $invalidTypes);

        if (empty($types)) {
            $output->writeln(self::ABORT_MESSAGE);
            return;
        }

        /** @var $cacheState \Magento\Framework\App\Cache\StateInterface */
        $cacheState =  $this->getObjectManager()->get('\Magento\Framework\App\Cache\StateInterface');
        $enabledTypes = [];

        try {
            foreach ($types as $type) {
                $cacheState->setEnabled($type, true);
                $enabledTypes[] = $type;
            }

            $cacheState->persist();
        } catch(\Exception $e) {
            $output->writeln(sprintf(self::EXCEPTION_MESSAGE, $e->getMessage()));
        }

        $output->writeln(sprintf(self::SUCCESS_MESSAGE, implode(', ', $types)));
    }
}