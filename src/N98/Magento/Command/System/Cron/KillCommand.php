<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace N98\Magento\Command\System\Cron;

use Magento\Cron\Model\Schedule;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Process\Process;

class KillCommand extends AbstractCronCommand
{
    protected function configure()
    {
        $help = <<<HELP
                This command requires a job code as an argument. 
                It will then kill the process for that job code.
                
                Please note that any job which should be killed has to run
                on the same machine as n98-magerun2.
                HELP;
        $this
            ->setName('sys:cron:kill')
            ->addOption(
                'timeout',
                't',
                InputOption::VALUE_OPTIONAL,
                'Timeout in seconds',
                5
            )
            ->addArgument(
                'job_code',
                InputArgument::OPTIONAL,
                'Job code input'
            )
            ->setDescription('Kill cron jobs by code')
            ->setHelp($help);

        parent::configure();
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $jobCode = $input->getArgument('job_code');

        // If no job code is provided, make an interactive selection
        if (!$jobCode) {
            $cronJobs = $this->getAllRunningJobs();
            if (count($cronJobs) > 0) {
                $helper = $this->getHelper('question');
                $jobCodes = $cronJobs->getColumnValues('job_code');
                $question = new ChoiceQuestion('Please select a job code to kill', $jobCodes);
                $jobCode = $helper->ask($input, $output, $question);
                $input->setArgument('job_code', $jobCode);
            }
        }

        parent::interact($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Check if the POSIX extension is available
        if (!extension_loaded('posix')) {
            $output->writeln(
                sprintf(
                    '<error>%s</error>',
                    'The POSIX extension is not available.'
                )
            );
            return Command::FAILURE;
        }

        $jobCode = $input->getArgument('job_code');

        // Get the hostname of the current machine
        $currentHostname = gethostname();
        $cronJobs = $this->getRunningJobByCode($jobCode);

        // If process-kill option is set, send a kill signal to the process
        foreach ($cronJobs as $job) {
            $pid = $job->getPid();
            if ($pid) {

                // Check if the job is running on the same machine
                if ($job->getHostname() !== $currentHostname) {
                    $output->writeln("<comment>The job $jobCode is not running on the current machine.</comment>");
                    continue;
                }

                // Create a new Process instance that sends a kill signal to the process
                $process = new Process(['kill', '-9', $pid]);
                $process->run();

                if (!$process->isSuccessful()) {
                    $output->writeln("<error>Failed to kill process $pid for job $jobCode</error>");
                    continue;
                }

                // Check if the process is still running every second for up to 5 seconds
                // 5s means 5 tries
                $tries = $this->getOption('timeout');
                while ($tries-- > 0) {
                    // Check if the process is still running
                    $process = new Process(['ps', '-p', $pid]);
                    $process->run();

                    if (!$process->isSuccessful()) {
                        // killed jobs are marked as error
                        $output->writeln("<info>Killed process $pid for job $jobCode</info>");
                        $job->setStatus(Schedule::STATUS_ERROR);
                        $job->save();

                        return Command::SUCCESS;
                    }

                    // Wait for a second before checking again
                    \sleep(1);
                }

                $output->writeln("<comment>The process $pid for job $jobCode is still running after sending the kill signal.</comment>");
                return Command::FAILURE;
            }
        }

        $output->writeln("<info>No process found to kill</info>");

        return Command::SUCCESS;
    }

    /**
     * @param $jobCode
     * @return \Magento\Cron\Model\Schedule
     */
    protected function getRunningJobByCode($jobCode): \Magento\Cron\Model\Schedule
    {
        return $this->cronScheduleCollection
            ->resetData()
            ->addFieldToFilter('job_code', $jobCode)
            ->addFieldToFilter('status', \Magento\Cron\Model\Schedule::STATUS_RUNNING)
            ->getFirstItem();
    }

    protected function getAllRunningJobs(): \Magento\Cron\Model\ResourceModel\Schedule\Collection
    {
        return $this->cronScheduleCollection
            ->addFieldToFilter('status', \Magento\Cron\Model\Schedule::STATUS_RUNNING)
            ->load();

        return $this->cronScheduleCollection;
    }
}
