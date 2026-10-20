<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Eav\AttributeGroup;

use Magento\Eav\Setup\EavSetup;
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

    /** @dataProvider sortOrders */
    public function testSortOrderBoundsBeforeMutation(string $sortOrder, bool $accepted): void
    {
        $setup = $this->createMock(EavSetup::class);
        $setup->method('convertToAttributeGroupCode')->willReturn('test-group');
        $setup->method('getAttributeGroupByCode')->willReturn(11);
        $setup->expects($accepted ? $this->once() : $this->never())
            ->method('addAttributeGroup')->with(4, 8, 'Test group', (int) $sortOrder);
        $command = $this->getMockBuilder(CreateCommand::class)
            ->onlyMethods(['initializeManagement', 'context', 'groupRows', 'clearEavCache'])->getMock();
        $command->method('initializeManagement')->willReturn(true);
        $command->method('context')->willReturn([$setup, 4, 8]);
        $command->expects($accepted ? $this->once() : $this->never())->method('groupRows')->willReturn([]);
        $command->expects($accepted ? $this->once() : $this->never())->method('clearEavCache');
        $input = new ArrayInput([
            'entityType' => 'catalog_product', 'attributeSetId' => '8', 'name' => 'Test group',
            '--sort-order' => $sortOrder,
        ]);
        $input->bind($command->getDefinition());
        if (!$accepted) {
            $this->expectException(\InvalidArgumentException::class);
            $this->expectExceptionMessage('between 0 and 32767');
        }
        $method = new \ReflectionMethod($command, 'execute');
        $this->assertSame(0, $method->invoke($command, $input, new BufferedOutput()));
    }

    public static function sortOrders(): array
    {
        return [['0', true], ['32767', true], ['32768', false], ['65535', false], ['2147483647', false], ['-1', false]];
    }
}
