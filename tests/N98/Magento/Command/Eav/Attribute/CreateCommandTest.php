<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Eav\Attribute;

use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use N98\Magento\Command\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class CreateCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->getApplication();
    }

    private function executeCommand(CreateCommand $command, array $options): int
    {
        $input = new ArrayInput(array_replace([
            'entityType' => 'catalog_product', 'attributeCode' => 'example', '--label' => 'Example',
        ], $options));
        $input->bind($command->getDefinition());
        $method = new \ReflectionMethod($command, 'execute');
        return $method->invoke($command, $input, new BufferedOutput());
    }

    public function testOutOfRangeSortOrderCannotReachMutation(): void
    {
        $config = $this->createMock(Config::class);
        $factory = $this->createMock(EavSetupFactory::class);
        $resource = $this->createMock(ResourceConnection::class);
        $factory->expects($this->never())->method('create');
        $resource->expects($this->never())->method('getConnection');
        $command = $this->getMockBuilder(CreateCommand::class)
            ->onlyMethods(['initializeManagement', 'entityId'])->getMock();
        $command->method('initializeManagement')->willReturn(true);
        $command->expects($this->never())->method('entityId');
        $command->inject($config, $factory, $resource);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('between 0 and 32767');
        $this->executeCommand($command, ['--sort-order' => '32768']);
    }

    public function testAssignmentFailureAfterAttributeInsertionRollsBack(): void
    {
        $config = $this->createMock(Config::class);
        $attribute = $this->createMock(Attribute::class);
        $attribute->method('getId')->willReturn(null);
        $config->method('getAttribute')->willReturn($attribute);
        $config->expects($this->once())->method('clear');
        $setup = $this->createMock(EavSetup::class);
        $inserted = false;
        $setup->expects($this->once())->method('addAttribute')
            ->with(4, 'example', $this->callback(function (array $data): bool {
                return $data['sort_order'] === 32767;
            }))->willReturnCallback(function () use (&$inserted, $setup) {
                $inserted = true;
                return $setup;
            });
        $failure = new \RuntimeException('Induced assignment failure');
        $setup->expects($this->once())->method('addAttributeToSet')->with(4, 8, 11, 'example', 32767)
            ->willReturnCallback(function () use (&$inserted, $failure) {
                $this->assertTrue($inserted, 'Assignment must fail after the attribute insertion call.');
                throw $failure;
            });
        $connection = $this->createMock(AdapterInterface::class);
        $connection->expects($this->once())->method('beginTransaction');
        $connection->expects($this->once())->method('rollBack');
        $connection->expects($this->never())->method('commit');
        $factory = $this->createMock(EavSetupFactory::class);
        $factory->method('create')->willReturn($setup);
        $resource = $this->createMock(ResourceConnection::class);
        $resource->method('getConnection')->willReturn($connection);
        $command = $this->getMockBuilder(CreateCommand::class)
            ->onlyMethods(['initializeManagement', 'entityId', 'attributeSet', 'attributeGroup', 'clearEavCache'])->getMock();
        $command->method('initializeManagement')->willReturn(true);
        $command->method('entityId')->willReturn(4);
        $command->method('attributeSet')->willReturn(['attribute_set_id' => 8]);
        $command->method('attributeGroup')->willReturn(['attribute_group_id' => 11]);
        $command->expects($this->never())->method('clearEavCache');
        $command->inject($config, $factory, $resource);
        try {
            $this->executeCommand($command, [
                '--sort-order' => '32767', '--attribute-set' => '8', '--attribute-group' => '11',
            ]);
            $this->fail('Expected the assignment failure to propagate.');
        } catch (\RuntimeException $exception) {
            $this->assertSame($failure, $exception);
        }
    }
}
