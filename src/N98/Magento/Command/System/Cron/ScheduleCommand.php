<?php

namespace N98\Magento\Command\System\Cron;

use Magento\Cron\Model\Schedule;
use Symfony\Component\Console\Helper\DialogHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ScheduleCommand extends AbstractCronCommand
{
    protected function configure()
    {
        $this
            ->setName('sys:cron:schedule')
            ->addArgument('job', InputArgument::OPTIONAL, 'Job code')
            ->setDescription('Schedule a cronjob for execution right now, by job code');
        $help = <<<HELP
If no `job` argument is passed you can select a job from a list.
HELP;
        $this->setHelp($help);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \Exception
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
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

        $output->write(
            '<info>Scheduling </info><comment>' . $jobConfig['instance'] . '::' . $jobConfig['method'] . '</comment> '
        );

        $createdAtTime   = $this->_timezone->scopeTimeStamp();
        $scheduledAtTime = $createdAtTime;

        $schedule = $this->_cronScheduleCollection->getNewEmptyItem();
        $schedule
            ->setJobCode($jobCode)
            ->setStatus(Schedule::STATUS_PENDING)
            ->setCreatedAt(strftime('%Y-%m-%d %H:%M:%S', $createdAtTime))
            ->setScheduledAt(strftime('%Y-%m-%d %H:%M', $scheduledAtTime))
            ->save();

        $output->writeln('<info>done</info>');
    }
}
