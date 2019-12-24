<?php

namespace N98\Magento\Command\Database;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ConsoleCommand
 * @package N98\Magento\Command\Database
 */
class ConsoleCommand extends AbstractDatabaseCommand
{
    protected function configure()
    {
        parent::configure();
        $this
            ->setName('db:console')
            ->setAliases(['mysql-client'])
            ->setDescription('Opens mysql client by database config from env.php');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int|void
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectDbSettings($output);

        $descriptorSpec = [
            0 => STDIN,
            1 => STDOUT,
            2 => STDERR,
        ];

        $exec = 'mysql ' . $this->getHelper('database')->getMysqlClientToolConnectionString();

        $pipes = [];
        $process = proc_open($exec, $descriptorSpec, $pipes);

        if (is_resource($process)) {
            proc_close($process);
        }
    }
}
