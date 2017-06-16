<?php

namespace N98\Magento\Command\System\Cron;

use N98\Magento\Command\AbstractMagentoCommand;
use Symfony\Component\Console\Helper\DialogHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractCronCommand extends AbstractMagentoCommand
{
    /**
     * @var \Magento\Framework\App\State
     */
    protected $state;

    /**
     * @var \Magento\Cron\Model\ConfigInterface
     */
    protected $cronConfig;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Cron\Model\ResourceModel\Schedule\Collection
     */
    protected $cronScheduleCollection;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $timezone;

    /**
     * @param \Magento\Framework\App\State $state
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Cron\Model\ConfigInterface $cronConfig
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Cron\Model\ResourceModel\Schedule\Collection $cronScheduleCollection
     */
    public function inject(
        \Magento\Framework\App\State $state,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Cron\Model\ConfigInterface $cronConfig,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Cron\Model\ResourceModel\Schedule\Collection $cronScheduleCollection
    ) {
        $this->state = $state;
        $this->cronConfig = $cronConfig;
        $this->scopeConfig = $scopeConfig;
        $this->cronScheduleCollection = $cronScheduleCollection;
        $this->timezone = $timezone;
    }

    /**
     * @return array
     */
    protected function getJobs()
    {
        $table = array();

        $jobs = $this->cronConfig->getJobs();

        foreach ($jobs as $jobGroupCode => $jobGroup) {
            foreach ($jobGroup as $job) {
                $row = [
                    'Job'   => isset($job['name']) ? $job['name'] : null,
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
        foreach ($this->cronConfig->getJobs() as $jobGroup) {
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
                'WD' => $array[4],
            ];
        }

        return ['m' => '-', 'h' => '-', 'D' => '-', 'M' => '-', 'WD' => '-'];
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param array $jobs
     * @return string
     * @throws \InvalidArgumentException
     * @throws \Exception
     */
    protected function askJobCode(InputInterface $input, OutputInterface $output, $jobs)
    {
        $question = array();
        foreach ($jobs as $key => $job) {
            $question[] = '<comment>[' . ($key + 1) . ']</comment> ' . $job['Job'] . PHP_EOL;
        }
        $question[] = '<question>Please select job: </question>' . PHP_EOL;

        /** @var $dialog DialogHelper */
        $dialog = $this->getHelper('dialog');
        $jobCode = $dialog->askAndValidate(
            $output,
            $question,
            function ($typeInput) use ($jobs) {
                if (!isset($jobs[$typeInput - 1])) {
                    throw new \InvalidArgumentException('Invalid job');
                }
                return $jobs[$typeInput - 1]['Job'];
            }
        );

        return $jobCode;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return array
     */
    protected function getJobForExecuteMethod(InputInterface $input, OutputInterface $output)
    {
        $jobCode = $input->getArgument('job');
        $jobs = $this->getJobs();

        if (!$jobCode) {
            $this->writeSection($output, 'Cronjob');
            $jobCode = $this->askJobCode($input, $output, $jobs);
        }

        $jobConfig = $this->getJobConfig($jobCode);

        if (empty($jobCode) || !isset($jobConfig['instance'])) {
            throw new \InvalidArgumentException('No job config found!');
        }

        $model = $this->getObjectManager()->get($jobConfig['instance']);

        if (!$model || !is_callable(array($model, $jobConfig['method']))) {
            throw new \RuntimeException(
                sprintf(
                    'Invalid callback: %s::%s does not exist',
                    $jobConfig['instance'],
                    $jobConfig['method']
                )
            );
        }

        return array($jobCode, $jobConfig, $model);
    }

    /**
     * Get timestamp used for time related database fields in the cron tables
     *
     * Note: The timestamp used will change from Magento 2.1.7 to 2.2.0 and
     *       these changes can be branched on Magento version in this method.
     *
     * @return int
     */
    protected function getCronTimestamp()
    {
        return $this->timezone->scopeTimeStamp();
    }
}
