<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Eav\Attribute;

use N98\Magento\Application;
use N98\Magento\Command\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class ViewCommandTest extends TestCase
{
    /**
     * The test subject
     * @var ViewCommand
     */
    private $command;

    /**
     * @var CommandTester
     */
    private $commandTester;

    /**
     * Initialize the command and the tester
     */
    protected function setUp(): void
    {
        /** @var Application $application */
        $application = $this->getApplication();
        $application->add(new ViewCommand());
        $this->command = $application->find('eav:attribute:view');
        $this->commandTester = new CommandTester($this->command);
    }

    /**
     * Ensure that the ViewCommand returns information about the supplied attribute
     * @test
     */
    public function execute()
    {
        $this->commandTester->execute(
            [
                'command'       => $this->command->getName(),
                'entityType'    => 'catalog_product',
                'attributeCode' => 'sku',
            ]
        );

        $result = $this->commandTester->getDisplay();

        $this->assertStringContainsString('sku', $result);
        $this->assertStringContainsString('catalog_product_entity', $result);
        $this->assertStringContainsString('Backend-Type', $result);
        $this->assertStringContainsString('static', $result);
    }

    /**
     * When the attribute doesn't exist, an exception should be thrown
     * @test
     */
    public function executeWithException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->commandTester->execute(
            [
                'command'       => $this->command->getName(),
                'entityType'    => 'catalog_product',
                'attributeCode' => 'foo_bar_attribute_that_should_never_ever_ever_exist',

            ]
        );
    }

    /**
     * Should return extra fields when it's a frontend attribute
     * @test
     */
    public function getTableInput()
    {
        $withoutFrontend = $this->command->getTableInput(false);
        $this->assertArrayHasKey('Name', $withoutFrontend);

        $withFrontend = $this->command->getTableInput(true);
        $this->assertArrayHasKey('Frontend/Label', $withFrontend);
    }

    /**
     * Should return an attribute model (from the abstract class)
     * @test
     */
    public function getAttribute()
    {
        $result = $this->command->getAttribute('catalog_product', 'sku');
        $this->assertInstanceOf('Magento\Eav\Model\Entity\Attribute\AbstractAttribute', $result);
    }
}
