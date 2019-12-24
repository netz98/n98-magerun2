<?php

namespace N98\Magento\Command\System\Cron;

use N98\Magento\Command\AbstractMagentoCommand;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

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
     * @var \Magento\Framework\App\ProductMetadataInterface $productMetadata
     */
    private $productMetadata;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $timezone;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $dateTime;

    /**
     * @param \Magento\Framework\App\State $state
     * @param \Magento\Cron\Model\ConfigInterface $cronConfig
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Cron\Model\ResourceModel\Schedule\Collection $cronScheduleCollection
     */
    public function inject(
        \Magento\Framework\App\State $state,
        \Magento\Cron\Model\ConfigInterface $cronConfig,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Cron\Model\ResourceModel\Schedule\Collection $cronScheduleCollection
    ) {
        $this->state = $state;
        $this->cronConfig = $cronConfig;
        $this->scopeConfig = $scopeConfig;
        $this->cronScheduleCollection = $cronScheduleCollection;
        $this->productMetadata = $productMetadata;
        $this->timezone = $timezone;
        $this->dateTime = $dateTime;
    }

    /**
     * @return array
     */
    protected function getJobs()
    {
        $table = [];

        $jobs = $this->cronConfig->getJobs();

        foreach ($jobs as $jobGroupCode => $jobGroup) {
            foreach ($jobGroup as $jobKey => $job) {
                $row = [
                    'Job'   => isset($job['name']) ? $job['name'] : $jobKey,
                    'Group' => $jobGroupCode,
                ];

                $row += $this->getSchedule($job);

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
            foreach ($jobGroup as $jobKey => $job) {
                if (isset($job['name']) && ($job['name'] == $jobCode || $jobKey == $jobCode)) {
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
        $choices = [];
        foreach ($jobs as $key => $job) {
            $choices[$key + 1] = $job['Job'];
        }

        $question = new ChoiceQuestion('<question>Please select a job:</question>', $choices);
        $question->setValidator(function ($typeInput) use ($jobs) {
            if (!isset($jobs[$typeInput - 1])) {
                throw new \InvalidArgumentException('Invalid job');
            }
            return $jobs[$typeInput - 1]['Job'];
        });

        /** @var $questionHelper QuestionHelper */
        $questionHelper = $this->getHelper('question');
        return $questionHelper->ask($input, $output, $question);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return array
     * @throws \Exception
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

        if (!$model || !is_callable([$model, $jobConfig['method']])) {
            throw new \RuntimeException(
                sprintf(
                    'Invalid callback: %s::%s does not exist',
                    $jobConfig['instance'],
                    $jobConfig['method']
                )
            );
        }

        return [$jobCode, $jobConfig, $model];
    }

    /**
     * Get timestamp used for time related database fields in the cron tables
     *
     * Note: The timestamp used will change from Magento 2.1.7 to 2.2.0 and
     *       these changes are branched by Magento version in this method.
     *
     * @return int
     */
    protected function getCronTimestamp()
    {
        /* @var $version string e.g. "2.1.7" */
        $version = $this->productMetadata->getVersion();

        if (version_compare($version, '2.2.0') >= 0) {
            return $this->dateTime->gmtTimestamp();
        }

        return $this->timezone->scopeTimeStamp();
    }
}
