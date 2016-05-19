<?php

namespace N98\Magento\Command\System\Cron;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use N98\Util\Console\Helper\Table\Renderer\RendererFactory;
use Magento\Cron\Model\Schedule;

class HistoryCommand extends AbstractCronCommand
{
    /**
     * @var array
     */
    protected $infos;

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
        if ($input->getOption('format') === null) {
            $this->writeSection($output, 'Last executed jobs');
        }

        $timezone = $input->getOption('timezone')
            ? $input->getOption('timezone') : $this->_scopeConfig->getValue('general/locale/timezone');

        if (!$input->getOption('format')) {
            $output->writeln('<info>Times shown in <comment>' . $timezone . '</comment></info>');
        }

        $date       = $this->getObjectManager()->create('Magento\Framework\Stdlib\DateTime\DateTime');
        $offset     = $date->calculateOffset($timezone);
        $this->_cronScheduleCollection
            ->addFieldToFilter('status', array('neq' => Schedule::STATUS_PENDING))
            ->addOrder('finished_at', \Magento\Framework\Data\Collection::SORT_ORDER_DESC);

        $table = array();
        foreach ($this->_cronScheduleCollection as $job) {
            $table[] = array(
                $job->getJobCode(),
                $job->getStatus(),
                $job->getFinishedAt() ? $date->gmtDate(null, $date->timestamp($job->getFinishedAt()) + $offset) : '',
            );
        }
        $this->getHelper('table')
            ->setHeaders(array('Job', 'Status', 'Finished'))
            ->renderByFormat($output, $table, $input->getOption('format'));
    }
}
