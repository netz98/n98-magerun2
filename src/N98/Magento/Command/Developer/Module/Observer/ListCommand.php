<?php

namespace N98\Magento\Command\Developer\Module\Observer;

use Exception;
use InvalidArgumentException;
use N98\Magento\Command\AbstractMagentoCommand;
use N98\Util\Console\Helper\Table\Renderer\RendererFactory;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

/**
 * Class ListCommand
 * @package N98\Magento\Command\Developer\Module\Observer
 */
class ListCommand extends AbstractMagentoCommand
{
    const SORT_WARNING_MESSAGE = '<warning>Sorting observers is a bad idea, call-order is important.</warning>';

    protected $areas = [
        'global',
        'adminhtml',
        'frontend',
        'crontab',
        'webapi_rest',
        'webapi_soap',
        'graphql',
        'doc',

        // 'admin' has been declared deprecated since 5448233
        // https://github.com/magento/magento2/commit/5448233#diff-5bc6336cfbfd5aeb18404416f508b6c4
        'admin',
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
     * {@inheritdoc}
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectMagento($output);
        $this->initMagento();

        $area = $input->getArgument('area');
        $eventFilter = $input->getArgument('event');

        if ($area === null || !in_array($area, $this->areas)) {
            $choices = [];
            foreach ($this->areas as $key => $area) {
                $choices[$key + 1] = '<comment>[' . $area . ']</comment> ';
            }

            $question = new ChoiceQuestion('<question>Please select an area:</question>', $choices);
            $question->setValidator(function ($areaIndex) {
                if (!in_array($areaIndex - 1, range(0, count($this->areas) - 1), true)) {
                    throw new InvalidArgumentException('Invalid selection.' . $areaIndex);
                }

                return $this->areas[$areaIndex - 1];
            });
            $area = $this->getHelper('question')->ask($input, $output, $question);
        }

        if ($input->getOption('format') === null) {
            $sectionHeader = 'Observers in [' . $area . '] area';

            if ($eventFilter !== null) {
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

            if ($eventFilter !== null && $eventName !== $eventFilter) {
                continue;
            }

            foreach ($observers as $observerName => $observerData) {
                if (!isset($observerData['instance'])) {
                    continue;
                }
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

        return 0;
    }
}
