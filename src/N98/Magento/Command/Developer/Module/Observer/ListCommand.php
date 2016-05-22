<?php

namespace N98\Magento\Command\Developer\Module\Observer;

use N98\Magento\Command\AbstractMagentoCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use N98\Util\Console\Helper\Table\Renderer\RendererFactory;

class ListCommand extends AbstractMagentoCommand
{
    const SORT_WARNING_MESSAGE = '<warning>Sorting observers is a bad idea, call-order is important.</warning>';

    protected $areas = [
        'global',
        'adminhtml',
        'frontend',
        'crontab'
    ];

    protected function configure()
    {
        $this
            ->setName('dev:module:observer:list')
            ->setDescription('Lists all registered observers')
            ->addArgument(
                'event',
                InputArgument::OPTIONAL,
                'Filter observers for specific event.'
            )
            ->addArgument(
                'area',
                InputArgument::OPTIONAL,
                'Filter observers in specific area. One of [' . implode(',', $this->areas) . ']'
            )
            ->addOption(
                'format',
                null,
                InputOption::VALUE_OPTIONAL,
                'Output Format. One of [' . implode(',', RendererFactory::getFormats()) . ']'
            )
            ->addOption(
                'sort',
                null,
                InputOption::VALUE_NONE,
                'Sort output ascending by event name'
            );
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectMagento($output);
        $this->initMagento();

        $area = $input->getArgument('area');
        $eventFilter = $input->getArgument('event');

        if (is_null($area) || !in_array($area, $this->areas)) {
            foreach ($this->areas as $key => $area) {
                $question[] = '<comment>[' . ($key + 1) . ']</comment> ' . $area . PHP_EOL;
            }

            $question[] = '<question>Please select an area:</question>';

            $area = $this->getHelper('dialog')->askAndValidate($output, $question, function ($areaIndex) {
                if (!in_array($areaIndex, range(1, count($this->areas)))) {
                    throw new \InvalidArgumentException('Invalid selection.');
                }

                return $this->areas[$areaIndex - 1];
            });
        }

        if ($input->getOption('format') === null) {
            $sectionHeader = 'Observers in [' . $area . '] area';

            if (!is_null($eventFilter)) {
                $sectionHeader .= ' registered for [' . $eventFilter . '] event';
            }

            $this->writeSection($output, $sectionHeader);
        }

        $observerConfig = $this->getObjectManager()
                            ->get('\Magento\Framework\Event\Config\Reader')
                            ->read($area);

        if (true === $input->getOption('sort')) {
            /**
             * n98-magerun comment:
             *  sorting for Observers is a bad idea because the order in which observers will be called is important.
             */
            if ($input->getOption('format') === null) {
                $output->writeln(self::SORT_WARNING_MESSAGE);
            }

            ksort($observerConfig);
        }

        $table = [];

        foreach ($observerConfig as $eventName => $observers) {
            $firstObserver = true;

            if (!is_null($eventFilter) && $eventName != $eventFilter) {
                continue;
            }

            foreach ($observers as $observerName => $observerData) {
                if ($firstObserver) {
                    $firstObserver = !$firstObserver;
                    $table[] = [$eventName, $observerName, $observerData['instance'] . '::' . $observerData['name']];
                } else {
                    $table[] = ['', $observerName, $observerData['instance'] . '::' . $observerData['name']];
                }
            }
        }

        // @todo Output is a bit ugly!?
        $this->getHelper('table')
                ->setHeaders(['Event', 'Observer name', 'Fires'])
                ->setRows($table)
                ->renderByFormat($output, $table, $input->getOption('format'));
    }
}
