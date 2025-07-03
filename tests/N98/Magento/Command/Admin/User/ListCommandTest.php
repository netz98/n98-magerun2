<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Admin\User;

use N98\Magento\Command\TestCase;

class ListCommandTest extends TestCase
{
    /**
     * @group current
     */
    public function testExecute()
    {
        $commandTester = $this->assertExecute('admin:user:list');

        // Simulate the output check
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('id', $output);
        $this->assertStringContainsString('username', $output);
        $this->assertStringContainsString('email', $output);
        $this->assertStringContainsString('status', $output);
        $this->assertStringContainsString('logdate', $output);
    }

    public function testDefaultSorting()
    {
        $commandTester = $this->assertExecute('admin:user:list');
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('logdate', $output); // Ensure new column is there
    }

    public function testSortByUsername()
    {
        $commandTester = $this->assertExecute(['command' => 'admin:user:list', '--sort'  => 'username']);
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('username', $output);
    }

    public function testSortByUserId()
    {
        $commandTester = $this->assertExecute(['command' => 'admin:user:list', '--sort'  => 'user_id']);
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('username', $output);
    }

    public function testSortByUserIdDesc()
    {
        $commandTester = $this->assertExecute(
            ['command' => 'admin:user:list', '--sort'  => 'user_id', '--sort-order' => 'desc']
        );
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('username', $output);
    }

    public function testSortByCreate()
    {
        $commandTester = $this->assertExecute(['command' => 'admin:user:list', '--sort'  => 'created']);
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('created', $output);
    }

    public function testUserWithNoLogDateDefined()
    {
        $commandTester = $this->assertExecute('admin:user:list');
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('logdate', $output);
    }

    /**
     * @dataProvider additionalColumnsProvider
     */
    public function testAdditionalColumns(array $columns, array $expectedHeaders)
    {
        $commandTester = $this->assertExecute([
            'command' => 'admin:user:list',
            '--columns' => implode(',', $columns),
        ]);
        $output = $commandTester->getDisplay();
        foreach ($expectedHeaders as $header) {
            $this->assertStringContainsStringIgnoringCase($header, $output);
        }
    }

    public function additionalColumnsProvider()
    {
        return [
            'single_column' => [ ['firstname'], ['firstname'] ],
            'multiple_columns' => [
                ['firstname', 'lastname', 'email'],
                ['firstname', 'lastname', 'email'] ],
            'all_columns' => [
                [
                    'user_id', 'firstname', 'lastname', 'email', 'username', 'password', 'created', 'modified',
                    'logdate', 'lognum', 'reload_acl_flag', 'is_active', 'extra', 'rp_token', 'rp_token_created_at',
                    'interface_locale', 'failures_num', 'first_failure', 'lock_expires'
                ],
                [
                    'id', 'firstname', 'lastname', 'email', 'username', 'password', 'created', 'modified',
                    'logdate', 'lognum', 'reload_acl_flag', 'status', 'extra', 'rp_token', 'rp_token_created_at',
                    'interface_locale', 'failures_num', 'first_failure', 'lock_expires'
                ]
            ],
            'mixed_case_columns' => [ ['FirstName', 'LastName'], ['firstname', 'lastname'] ],
        ];
    }
}
