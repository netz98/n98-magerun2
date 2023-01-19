<?php

namespace N98\Magento\Command\System\Cron;

use Magento\Cron\Model\Schedule;
use N98\Util\Console\Helper\Table\Renderer\RendererFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class HistoryCommand
 * @package N98\Magento\Command\System\Cron
 */
class HistoryCommand extends AbstractCronCommand
{
    protected function configure()
    {
        $this
            ->setName('sys:cron:history')
            ->setDescription('Last executed cronjobs with status.')
            ->addOption(
                'timezone',
                null,
                InputOption::VALUE_OPTIONAL,
                'Timezone to show finished at in'
            )
            ->addOption(
                'format',
                null,
                InputOption::VALUE_OPTIONAL,
                'Output Format. One of [' . implode(',', RendererFactory::getFormats()) . ']'
            );
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('format') === null) {
            $this->writeSection($output, 'Last executed jobs');
        }

        $timezone = $input->getOption('timezone')
            ? $input->getOption('timezone') : $this->scopeConfig->getValue('general/locale/timezone');

        // If Magento config contains a invalid timezone
        if (empty($timezone)) {
            $timezone = 'UTC';
        }

        if (!$input->getOption('format')) {
            $output->writeln('<info>Times shown in <comment>' . $timezone . '</comment></info>');
        }

        $dateFactory = $this->getObjectManager()->get('Magento\Framework\Stdlib\DateTime\DateTimeFactory');
        $date = $dateFactory->create();
        $offset = $date->calculateOffset($timezone);

        $this->cronScheduleCollection
            ->addFieldToFilter('status', ['neq' => Schedule::STATUS_PENDING])
            ->addOrder('finished_at', \Magento\Framework\Data\Collection::SORT_ORDER_DESC);

        $table = [];
        foreach ($this->cronScheduleCollection as $job) {
            $finishedAt = '';

            if ($job->getFinishedAt()) {
                $finishedAt = date(
                    'Y-m-d H:i:s',
                    strtotime($job->getFinishedAt()) + $offset
                );
            }

            $table[] = [
                $job->getJobCode(),
                $job->getStatus(),
                $finishedAt
            ];
        }
        $this->getHelper('table')
            ->setHeaders(['Job', 'Status', 'Finished'])
            ->renderByFormat($output, $table, $input->getOption('format'));

        return Command::SUCCESS;
    }
}
