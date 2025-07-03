<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Database;

use N98\Magento\Command\CommandAware;
use N98\Magento\Command\TestCase;
use N98\Util\Console\Helper\DatabaseHelper;

/**
 * Class AddDefaultAuthorizationCommandTest
 */
class AddDefaultAuthorizationEntriesCommandTest extends TestCase
{
    public function testExecute()
    {
        $this->assertDisplayContains(
            'db:add-default-authorization-entries',
            'Default authorization tables OK'
        );
    }

    public function testWithEmptyRoleTable()
    {
        $dbHelper = $this->getDatabaseHelper();
        $roleTableName = $dbHelper->getTableName('authorization_role');
        $dbHelper->getConnection()->query('DELETE FROM ' . $roleTableName);

        $this->assertDisplayContains(
            'db:add-default-authorization-entries',
            'Default authorization role inserted'
        );
    }

    public function testWithEmptyRuleTable()
    {
        $dbHelper = $this->getDatabaseHelper();
        $ruleTableName = $dbHelper->getTableName('authorization_rule');
        $dbHelper->getConnection()->query('DELETE FROM ' . $ruleTableName);

        $this->assertDisplayContains(
            'db:add-default-authorization-entries',
            'Default authorization rule inserted'
        );
    }

    /**
     * @return DatabaseHelper
     */
    private function getDatabaseHelper()
    {
        $command = $this->getApplication()->find('db:add-default-authorization-entries');

        $helper = $command->getHelper('database');
        if ($helper instanceof CommandAware) {
            $helper->setCommand($command);
        }

        return $helper;
    }
}
