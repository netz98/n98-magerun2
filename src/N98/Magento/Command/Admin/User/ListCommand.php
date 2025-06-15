<?php

namespace N98\Magento\Command\Admin\User;

use N98\Util\Console\Helper\Table\Renderer\RendererFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ListCommand extends AbstractAdminUserCommand
{
    protected function configure()
    {
        $this
            ->setName('admin:user:list')
            ->setDescription('List admin users.')
            ->addOption(
                'format',
                null,
                InputOption::VALUE_OPTIONAL,
                'Output Format. One of [' . implode(',', RendererFactory::getFormats()) . ']'
            )
            ->addOption(
                'sort',
                null,
                InputOption::VALUE_OPTIONAL,
                'Sort user list by a field (e.g., user_id, username, email, logdate)'
            )
            ->addOption(
                'sort-order',
                null,
                InputOption::VALUE_OPTIONAL,
                'Sort order direction (asc or desc). Default is asc',
                'asc'
            )
            ->addOption(
                'columns',
                null,
                InputOption::VALUE_OPTIONAL,
                'Comma-separated list of columns to display. Available: user_id, firstname, lastname, email, username, password, created, modified, logdate, lognum, reload_acl_flag, is_active, extra, rp_token, rp_token_created_at, interface_locale, failures_num, first_failure, lock_expires',
                null
            );
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectMagento($output, true);
        if (!$this->initMagento()) {
            return Command::FAILURE;
        }

        $userCollection = $this->userModel->getCollection();

        $sortField = $input->getOption('sort') ?: 'user_id';
        if ($sortField === 'status') {
            $sortField = 'is_active';
        }
        $sortOrder = strtolower($input->getOption('sort-order')) === 'desc' ? 'DESC' : 'ASC';
        $userCollection->setOrder($sortField, $sortOrder);

        $availableColumns = [
            'user_id' => 'user_id',
            'firstname' => 'firstname',
            'lastname' => 'lastname',
            'email' => 'email',
            'username' => 'username',
            'password' => 'password',
            'created' => 'created',
            'modified' => 'modified',
            'logdate' => 'logdate',
            'lognum' => 'lognum',
            'reload_acl_flag' => 'reload_acl_flag',
            'is_active' => 'status',
            'extra' => 'extra',
            'rp_token' => 'rp_token',
            'rp_token_created_at' => 'rp_token_created_at',
            'interface_locale' => 'interface_locale',
            'failures_num' => 'failures_num',
            'first_failure' => 'first_failure',
            'lock_expires' => 'lock_expires',
        ];

        $defaultColumns = ['user_id', 'username', 'email', 'is_active', 'logdate'];
        $columnsOpt = $input->getOption('columns');
        $sortField = $input->getOption('sort') ?: 'user_id';
        if ($sortField === 'status') {
            $sortField = 'is_active';
        }

        // If columns are not defined, but sort is set and not in default columns, add it
        if (!$columnsOpt && $sortField && !in_array($sortField, $defaultColumns, true) && isset($availableColumns[$sortField])) {
            $defaultColumns[] = $sortField;
        }

        $columns = $columnsOpt ? array_map('trim', explode(',', $columnsOpt)) : $defaultColumns;
        // Normalize columns to lowercase for matching
        $columns = array_map('strtolower', $columns);
        // Validate columns
        $columns = array_filter($columns, function ($col) use ($availableColumns) {
            return isset($availableColumns[$col]);
        });

        if (empty($columns)) {
            $output->writeln('<error>No valid columns specified.</error>');
            return Command::FAILURE;
        }

        $headers = array_map(function ($col) use ($availableColumns) {
            return $availableColumns[$col];
        }, $columns);

        $table = [];
        foreach ($userCollection as $user) {
            $row = [];
            foreach ($columns as $col) {
                switch ($col) {
                    case 'user_id':
                        $row[] = $user->getId();
                        break;
                    case 'is_active':
                        $row[] = $user->getIsActive() ? 'active' : 'inactive';
                        break;
                    default:
                        $row[] = $user->getData($col);
                        break;
                }
            }
            $table[] = $row;
        }

        $this->getHelper('table')
            ->setHeaders($headers)
            ->renderByFormat($output, $table, $input->getOption('format'));

        return Command::SUCCESS;
    }
}
