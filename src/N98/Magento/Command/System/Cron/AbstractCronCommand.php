<?php

namespace N98\Magento\Command\System\Cron;

use N98\Magento\Command\AbstractMagentoCommand;

abstract class AbstractCronCommand extends AbstractMagentoCommand
{
    /**
     * @var \Magento\Framework\App\State
     */
    protected $_state;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * @var \Magento\Cron\Model\ConfigInterface
     */
    protected $_cronConfig;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Cron\Model\ResourceModel\Schedule\Collection
     */
    protected $_cronScheduleCollection;

    /**
     * @param \Magento\Framework\App\State $state
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Cron\Model\ConfigInterface $cronConfig
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Cron\Model\ResourceModel\Schedule\Collection $cronScheduleCollection
     */
    public function inject(
        \Magento\Framework\App\State $state,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Cron\Model\ConfigInterface $cronConfig,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Cron\Model\ResourceModel\Schedule\Collection $cronScheduleCollection
    ) {
        $this->_state = $state;
        $this->_eventManager = $eventManager;
        $this->_cronConfig = $cronConfig;
        $this->_scopeConfig = $scopeConfig;
        $this->_cronScheduleCollection = $cronScheduleCollection;
    }

    /**
     * @return array
     */
    protected function getJobs()
    {
        $table = array();

        $jobs = $this->_cronConfig->getJobs();

        foreach ($jobs as $jobGroupCode => $jobGroup) {
            foreach ($jobGroup as $job) {
                $row = [
                    'Job'   => $job['name'],
                    'Group' => $jobGroupCode,
                ];

                $row = $row + $this->getSchedule($job);

                $table[] = $row;
            }
        }

        usort($table, function ($a, $b) {
            return strcmp($a['Job'], $b['Job']);
        });

        return $table;
    }

    /**
     * @param string $jobCode
     * @return array
     */
    protected function getJobConfig($jobCode)
    {
        foreach ($this->_cronConfig->getJobs() as $jobGroup) {
            foreach ($jobGroup as $job) {
                if ($job['name'] == $jobCode) {
                    return $job;
                }
            }
        }

        return [];
    }

    /**
     * @param array $job
     * @return array
     */
    protected function getSchedule(array $job)
    {
        if (isset($job['schedule'])) {
            $expr = $job['schedule'];
            if ($expr == 'always') {
                return ['m' => '*', 'h' => '*', 'D' => '*', 'M' => '*', 'WD' => '*'];
            }

            $schedule = $this->getObjectManager()->create('Magento\Cron\Model\Schedule');
            $schedule->setCronExpr($expr);
            $array = $schedule->getCronExprArr();

            return [
                'm'  => $array[0],
                'h'  => $array[1],
                'D'  => $array[2],
                'M'  => $array[3],
                'WD' => $array[4]
            ];
        }

        return ['m' => '-', 'h' => '-', 'D' => '-', 'M' => '-', 'WD' => '-'];
    }
}
