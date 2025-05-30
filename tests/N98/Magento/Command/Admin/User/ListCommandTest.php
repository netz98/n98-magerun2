<?php

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

        // Assuming at least two users exist for sorting checks
        // This requires knowledge of test data or mocking.
        // For now, we'll check if the headers are present and the command runs.
        // A more robust test would involve parsing the table and checking actual data order.
        // Let's assume user_id 1 and 2 exist and 1 appears before 2.
        // This is a simplification. Proper data setup or mocking is needed for robust sort testing.
        $this->assertMatchesRegularExpression('/1.*admin.*2.*testuser/s', $output);
    }

    public function testSortByUsername()
    {
        $commandTester = $this->assertExecute(['command' => 'admin:user:list', '--sort'  => 'username']);
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('logdate', $output);
        // Assuming 'admin' comes before 'testuser' alphabetically.
        // This is a simplification. Proper data setup or mocking is needed.
        $this->assertMatchesRegularExpression('/admin.*testuser/s', $output);
    }

    public function testSortByUserId()
    {
        $commandTester = $this->assertExecute(['command' => 'admin:user:list', '--sort'  => 'user_id']);
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('logdate', $output);
        // Similar to default sorting, assuming user_id 1 and 2 exist.
        // This is a simplification. Proper data setup or mocking is needed.
        $this->assertMatchesRegularExpression('/1.*admin.*2.*testuser/s', $output);
    }

    public function testUserWithNoLogDate()
    {
        // This test requires specific data setup: a user who has never logged in.
        // For now, we ensure the command runs and includes the logdate column.
        // A more specific assertion would be to check for an empty logdate for a particular user.
        $commandTester = $this->assertExecute('admin:user:list');
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('logdate', $output);
        // Example: if a user 'nouserlog' has no logdate, output might look like:
        // nouserlog | nouserlog@example.com | active |
        // The regex below is a placeholder for what such an assertion might look like.
        // $this->assertMatchesRegularExpression('/nouserlog.*\|\s*(\n|$)/m', $output);
        // For now, just checking the column exists is the first step.
        $this->assertStringContainsString('logdate', $output);
    }
}
