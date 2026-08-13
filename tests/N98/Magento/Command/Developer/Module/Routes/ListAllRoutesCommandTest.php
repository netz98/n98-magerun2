<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Developer\Module\Routes;

use N98\Magento\Command\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class ListAllRoutesCommandTest extends TestCase
{
    public function testExecute()
    {
        $application = $this->getApplication();
        $application->add(new ListAllRoutesCommand());

        $command = $this->getApplication()->find('routes:api:list');
        $commandTester = new CommandTester($command);

        try {
            $commandTester->execute(
                [
                    'command' => $command->getName(),
                ]
            );
        } catch (\Exception $e) {
            $this->assertMatchesRegularExpression("/(not initialized|Could not determine Magento version)/", $e->getMessage());
        }

        $output = $commandTester->getDisplay();
        if (strpos($output, 'Magento application was not initialized') === false &&
            strpos($output, 'No routes found') === false &&
            strpos($output, 'Could not retrieve Magento 2 routes') === false &&
            strpos($output, 'Could not determine Magento version') === false) {
            $this->assertNotEmpty($output, "Command output should not be empty if no initialization error occurs.");
        } else {
            $this->assertMatchesRegularExpression("/(not initialized|No routes found|Could not retrieve Magento 2 routes|Could not determine Magento version)/", $output);
        }

        $this->assertStringContainsString('Lists all registered API routes', $command->getDescription());
    }

    public function testExecuteWithFilters()
    {
        $application = $this->getApplication();
        $application->add(new ListAllRoutesCommand());

        $command = $this->getApplication()->find('routes:api:list');
        $commandTester = new CommandTester($command);

        try {
            // Run with method filter
            $commandTester->execute([
                'command' => $command->getName(),
                '--method' => 'GET',
            ]);
            $output = $commandTester->getDisplay();
            if (strpos($output, 'Fetching API routes') !== false && strpos($output, 'No specific API routes found') === false) {
                $this->assertStringContainsString('GET', $output);
                $this->assertStringNotContainsString('POST', $output);
            }

            // Run with path filter
            $commandTester->execute([
                'command' => $command->getName(),
                '--path' => 'carts',
            ]);
            $output = $commandTester->getDisplay();
            if (strpos($output, 'Fetching API routes') !== false && strpos($output, 'No specific API routes found') === false) {
                $this->assertStringContainsString('carts', $output);
            }
        } catch (\Exception $e) {
            $this->assertMatchesRegularExpression("/(not initialized|Could not determine Magento version)/", $e->getMessage());
        }
    }

    public function testCommandName()
    {
        $command = new ListAllRoutesCommand();
        $this->assertEquals('routes:api:list', $command->getName());
    }
}
