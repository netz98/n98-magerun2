<?php

namespace N98\Magento\Command\System\Cron;

use Exception;
use RuntimeException;
use Magento\Cron\Model\Schedule;
use Magento\Framework\App\Area;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RunCommand extends AbstractCronCommand
{
    protected function configure()
    {
        $this
            ->setName('sys:cron:run')
            ->addArgument('job', InputArgument::OPTIONAL, 'Job code')
            ->setDescription('Runs a cronjob by job code');
        $help = <<<HELP
If no `job` argument is passed you can select a job from a list.
See it in action: http://www.youtube.com/watch?v=QkzkLgrfNaM
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

        list($jobCode, $jobConfig, $model) = $this->getJobForExecuteMethod($input, $output);

        $callback = array($model, $jobConfig['method']);

        $output->write(
            '<info>Run </info><comment>' . $jobConfig['instance'] . '::' . $jobConfig['method'] . '</comment> '
        );

        /* @var $schedule \Magento\Cron\Model\Schedule */
        $schedule = $this->cronScheduleCollection->getNewEmptyItem();
        $schedule
            ->setJobCode($jobCode)
            ->setStatus(Schedule::STATUS_RUNNING)
            ->setExecutedAt(strftime('%Y-%m-%d %H:%M:%S', $this->timezone->scopeTimeStamp()))
            ->save();

        try {
            $this->state->emulateAreaCode(Area::AREA_CRONTAB, $callback, array($schedule));

            $schedule
                ->setStatus(Schedule::STATUS_SUCCESS)
                ->setFinishedAt(strftime('%Y-%m-%d %H:%M:%S', $this->timezone->scopeTimeStamp()))
                ->save();
        } catch (Exception $e) {
            $schedule
                ->setStatus(Schedule::STATUS_ERROR)
                ->setMessages($e->getMessage())
                ->setFinishedAt(strftime('%Y-%m-%d %H:%M:%S', $this->timezone->scopeTimeStamp()))
                ->save();
        }
        
        if (isset($e)) {
            throw new RuntimeException(
                sprintf('Cron-job "%s" threw exception %s', $jobCode, get_class($e)),
                0,
                $e
            );
        }

        $output->writeln('<info>done</info>');
    }
}
