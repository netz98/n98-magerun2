<?php

namespace N98\Magento\Command\System\Cron;

use Magento\Cron\Model\Schedule;
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
        $this->state->setAreaCode(Area::AREA_CRONTAB);
        $objectManager = $this->getObjectManager();
        $configLoader = $objectManager->get('Magento\Framework\ObjectManager\ConfigLoaderInterface');
        $objectManager->configure($configLoader->load(Area::AREA_CRONTAB));

        list($jobCode, $jobConfig) = $this->getJobForExecuteMethod($input, $output);

        $output->write(
            '<info>Scheduling </info><comment>' . $jobConfig['instance'] . '::' . $jobConfig['method'] . '</comment> '
        );

        $createdAtTime = $this->timezone->scopeTimeStamp();
        $scheduledAtTime = $createdAtTime;

        /* @var $schedule \Magento\Cron\Model\Schedule */
        $schedule = $this->cronScheduleCollection->getNewEmptyItem();
        $schedule
            ->setJobCode($jobCode)
            ->setStatus(Schedule::STATUS_PENDING)
            ->setCreatedAt(strftime('%Y-%m-%d %H:%M:%S', $createdAtTime))
            ->setScheduledAt(strftime('%Y-%m-%d %H:%M', $scheduledAtTime))
            ->save();

        $output->writeln('<info>done</info>');
    }
}
