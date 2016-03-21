<?php

namespace N98\Magento\Command\System\Cron;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Cron\Model\Schedule;

class RunCommand extends AbstractCronCommand
{
    const REGEX_RUN_MODEL = '#^([a-z0-9_]+/[a-z0-9_]+)::([a-z0-9_]+)$#i';
    /**
     * @var array
     */
    protected $infos;

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
        $jobCode = $input->getArgument('job');
        $jobs = $this->getJobs();

        if (!$jobCode) {
            $this->writeSection($output, 'Cronjob');
            $jobCode = $this->askJobCode($input, $output, $jobs);
        }

        $this->_state->setAreaCode('crontab');

        $jobConfig = $this->getJobConfig($jobCode);

        if (empty($jobCode)|| !isset($jobConfig['instance'])) {
            throw new \InvalidArgumentException('No job config found!');
        }

        $model = $this->getObjectManager()->get($jobConfig['instance']);

        if (!$model || !method_exists($model, $jobConfig['method'])) {
            throw new \RuntimeException(
                sprintf(
                    'Invalid callback: %s::%s does not exist',
                    $jobConfig['instance'],
                    $jobConfig['method']
                )
            );
        }

        $callback = array($model, $jobConfig['method']);

        $output->write(
            '<info>Run </info><comment>' . $jobConfig['instance'] . '::' . $jobConfig['method'] . '</comment> '
        );

        try {
            $schedule = $this->_cronScheduleCollection->getNewEmptyItem();
            $schedule
                ->setJobCode($jobCode)
                ->setStatus(Schedule::STATUS_RUNNING)
                ->setExecutedAt(strftime('%Y-%m-%d %H:%M:%S', time()))
                ->save();

            call_user_func_array($callback, array($schedule));

            $schedule
                ->setStatus(Schedule::STATUS_SUCCESS)
                ->setFinishedAt(strftime('%Y-%m-%d %H:%M:%S', time()))
                ->save();
        } catch (Exception $e) {
            $schedule
                ->setStatus(Schedule::STATUS_ERROR)
                ->setMessages($e->getMessage())
                ->setFinishedAt(strftime('%Y-%m-%d %H:%M:%S', time()))
                ->save();
        }

        $output->writeln('<info>done</info>');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param array $jobs
     * @return mixed
     * @throws \InvalidArgumentException
     * @throws \Exception
     */
    protected function askJobCode(InputInterface $input, OutputInterface $output, $jobs)
    {
        foreach ($jobs as $key => $job) {
            $question[] = '<comment>[' . ($key+1) . ']</comment> ' . $job['Job'] . PHP_EOL;
        }
        $question[] = '<question>Please select job: </question>' . PHP_EOL;

        $jobCode = $this->getHelperSet()->get('dialog')->askAndValidate(
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
}
