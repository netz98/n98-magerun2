<?php

namespace N98\Magento\Command\Database;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class AddDefaultAuthorizationRoles
 */
class AddDefaultAuthorizationRoles extends AbstractDatabaseCommand
{
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('db:add-default-authorization-entries')
            ->setDescription('Add default entry to authorization_role and authorization_rule tables.')
            ->setHelp('See https://github.com/netz98/n98-magerun2/issues/351');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int|void
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectDbSettings($output);

        $dbHelper = $this->getDatabaseHelper();
        $connection =  $dbHelper->getConnection($output, true);

        $roleTableName = $dbHelper->getTableName('authorization_role');
        $ruleTableName = $dbHelper->getTableName('authorization_rule');

        $statement = $connection->query('SELECT COUNT(*) AS cnt FROM ' . $roleTableName);
        $cnt = (int) $statement->fetchColumn(0);

        $actionCount = 0;

        if ($cnt === 0) {
            $sql = 'INSERT INTO ' . $roleTableName
                . ' (role_id, parent_id, tree_level, sort_order, role_type, user_id, user_type, role_name) '
                . 'VALUES (1, 0, 1, 1, \'G\', 0, \'2\', \'Administrators\')';

            $result = $connection->query($sql);

            if (!$result) {
                $output->writeln('<error>Cannot insert authorization role</error>');

                return 1;
            }

            $output->writeln('<info>Default authorization role inserted</info>');
            $actionCount++;
        }

        $statement = $connection->query('SELECT COUNT(*) AS cnt FROM ' . $ruleTableName);
        $cnt = (int) $statement->fetchColumn(0);

        if ($cnt === 0) {
            $sql = 'INSERT INTO ' . $ruleTableName
                . ' (rule_id, role_id, resource_id, privileges, permission) '
                . 'VALUES (1, 1, \'Magento_Backend::all\', null, \'allow\')';

            $result = $connection->query($sql);

            if (!$result) {
                $output->writeln('<error>Cannot insert authorization rule</error>');

                return 1;
            }

            $output->writeln('<info>Default authorization rule inserted</info>');

            $actionCount++;
        }

        if ($actionCount === 0) {
            $output->writeln('<info>Default authorization tables</info> <comment>OK</comment>');
        }

        return 0;
    }
}
