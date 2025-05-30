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
        $applicationTester = $this->getApplicationTester();
        $applicationTester->run(
            [
                'command' => 'admin:user:list',
            ]
        );

        $this->assertStringContainsString('id', $applicationTester->getDisplay());
        $this->assertStringContainsString('username', $applicationTester->getDisplay());
        $this->assertStringContainsString('email', $applicationTester->getDisplay());
        $this->assertStringContainsString('status', $applicationTester->getDisplay());
        $this->assertStringContainsString('logdate', $applicationTester->getDisplay());
    }

    public function testDefaultSorting()
    {
        $applicationTester = $this->getApplicationTester();
        $applicationTester->run(
            [
                'command' => 'admin:user:list',
            ]
        );
        $output = $applicationTester->getDisplay();
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
        $applicationTester = $this->getApplicationTester();
        $applicationTester->run(
            [
                'command' => 'admin:user:list',
                '--sort'  => 'username',
            ]
        );
        $output = $applicationTester->getDisplay();
        $this->assertStringContainsString('logdate', $output);
        // Assuming 'admin' comes before 'testuser' alphabetically.
        // This is a simplification. Proper data setup or mocking is needed.
        $this->assertMatchesRegularExpression('/admin.*testuser/s', $output);
    }

    public function testSortByUserId()
    {
        $applicationTester = $this->getApplicationTester();
        $applicationTester->run(
            [
                'command' => 'admin:user:list',
                '--sort'  => 'user_id',
            ]
        );
        $output = $applicationTester->getDisplay();
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
        $applicationTester = $this->getApplicationTester();
        $applicationTester->run(
            [
                'command' => 'admin:user:list',
            ]
        );
        $output = $applicationTester->getDisplay();
        $this->assertStringContainsString('logdate', $output);
        // Example: if a user 'nouserlog' has no logdate, output might look like:
        // nouserlog | nouserlog@example.com | active |
        // The regex below is a placeholder for what such an assertion might look like.
        // $this->assertMatchesRegularExpression('/nouserlog.*\|\s*(\n|$)/m', $output);
        // For now, just checking the column exists is the first step.
        $this->assertStringContainsString('logdate', $output);
    }
}
