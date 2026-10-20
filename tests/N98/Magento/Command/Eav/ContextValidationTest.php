<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Eav;

use Magento\Eav\Setup\EavSetup;
use N98\Magento\Command\Eav\AttributeGroup\ListCommand;
use PHPUnit\Framework\TestCase;

class ContextValidationTest extends TestCase
{
    /** @dataProvider invalidIds */
    public function testRejectsInvalidIdsBeforeLookup($id): void
    {
        $setup = $this->createMock(EavSetup::class);
        $setup->expects($this->never())->method('getAttributeSet');
        $method = new \ReflectionMethod(ListCommand::class, 'attributeSet');
        $method->setAccessible(true);
        $this->expectException(\InvalidArgumentException::class);
        $method->invoke(new ListCommand(), $setup, 4, $id);
    }

    public static function invalidIds(): array
    {
        return [[0], [-1], ['Default'], ['1.5'], ['1e2'], [''], ['999999999999999999999999']];
    }

    /** @dataProvider missingOrMismatchedSets */
    public function testRejectsMissingOrMismatchedSet(array $set): void
    {
        $setup = $this->createMock(EavSetup::class);
        $setup->method('getAttributeSet')->with(4, 8)->willReturn($set);
        $method = new \ReflectionMethod(ListCommand::class, 'attributeSet');
        $method->setAccessible(true);
        $this->expectException(\InvalidArgumentException::class);
        $method->invoke(new ListCommand(), $setup, 4, '8');
    }

    public static function missingOrMismatchedSets(): array
    {
        return [[[]], [['entity_type_id' => 1, 'attribute_set_id' => 8]]];
    }

    /** @dataProvider missingOrMismatchedGroups */
    public function testRejectsMissingOrMismatchedGroup(array $group): void
    {
        $setup = $this->createMock(EavSetup::class);
        $setup->method('getAttributeGroup')->with(4, 8, 11)->willReturn($group);
        $method = new \ReflectionMethod(ListCommand::class, 'attributeGroup');
        $method->setAccessible(true);
        $this->expectException(\InvalidArgumentException::class);
        $method->invoke(new ListCommand(), $setup, 4, 8, '11');
    }

    public static function missingOrMismatchedGroups(): array
    {
        return [[[]], [['attribute_set_id' => 9, 'attribute_group_id' => 11]]];
    }
}
