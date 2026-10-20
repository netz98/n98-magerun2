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
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class DeleteCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->getApplication();
    }

    /** @dataProvider deletionCases */
    public function testDeletionSafety(bool $force, bool $default, bool $populated, bool $deleted, bool $effectiveDefault = false): void
    {
        $setup = $this->createMock(EavSetup::class);
        $setup->method('getDefaultAttributeGroupId')->willReturn($effectiveDefault ? 11 : 10);
        $setup->expects($deleted ? $this->once() : $this->never())
            ->method('removeAttributeGroup')->with(4, 8, 11);
        $command = $this->getMockBuilder(DeleteCommand::class)
            ->onlyMethods(['initializeManagement', 'context', 'attributeGroup', 'assignedAttributes', 'clearEavCache'])
            ->getMock();
        $command->method('initializeManagement')->willReturn(true);
        $command->method('context')->willReturn([$setup, 4, 8]);
        $command->method('attributeGroup')->willReturn(['attribute_group_id' => 11, 'attribute_set_id' => 8, 'default_id' => $default ? 1 : 0]);
        $command->method('assignedAttributes')->willReturn($populated ? [['attribute_id' => 99]] : []);
        $input = new ArrayInput(['entityType' => 'catalog_product', 'attributeSetId' => '8', 'attributeGroupId' => '11', '--force' => $force]);
        $input->bind($command->getDefinition());
        $input->setInteractive(false);
        if ($default || $effectiveDefault || $populated) {
            $this->expectException(\InvalidArgumentException::class);
            $this->expectExceptionMessage(($default || $effectiveDefault) ? 'Cannot delete a default' : 'Cannot delete a populated');
        }
        $method = new \ReflectionMethod($command, 'execute');
        $method->setAccessible(true);
        $status = $method->invoke($command, $input, new BufferedOutput());
        $this->assertSame($deleted ? 0 : 1, $status);
    }

    public static function deletionCases(): array
    {
        return [
            'noninteractive needs force' => [false, false, false, false],
            'force deletes empty group' => [true, false, false, true],
            'force cannot delete default' => [true, true, false, false],
            'force cannot delete effective default' => [true, false, false, false, true],
            'force cannot delete populated' => [true, false, true, false],
            'default without force' => [false, true, false, false],
            'populated without force' => [false, false, true, false],
        ];
    }

    /** @dataProvider confirmationAnswers */
    public function testInteractiveConfirmation(string $answer, bool $deleted): void
    {
        $setup = $this->createMock(EavSetup::class);
        $setup->method('getDefaultAttributeGroupId')->willReturn(10);
        $setup->expects($deleted ? $this->once() : $this->never())->method('removeAttributeGroup')->with(4, 8, 11);
        $command = $this->getMockBuilder(DeleteCommand::class)
            ->onlyMethods(['initializeManagement', 'context', 'attributeGroup', 'assignedAttributes', 'clearEavCache'])
            ->getMock();
        $command->method('initializeManagement')->willReturn(true);
        $command->method('context')->willReturn([$setup, 4, 8]);
        $command->method('attributeGroup')->willReturn(['attribute_set_id' => 8, 'default_id' => 0]);
        $command->method('assignedAttributes')->willReturn([]);
        $command->setHelperSet(new HelperSet(['question' => new QuestionHelper()]));
        $input = new ArrayInput(['entityType' => 'catalog_product', 'attributeSetId' => '8', 'attributeGroupId' => '11']);
        $input->bind($command->getDefinition());
        $input->setInteractive(true);
        $stream = fopen('php://memory', 'r+');
        fwrite($stream, $answer . "\n");
        rewind($stream);
        $input->setStream($stream);
        try {
            $method = new \ReflectionMethod($command, 'execute');
            $method->setAccessible(true);
            $this->assertSame($deleted ? 0 : 1, $method->invoke($command, $input, new BufferedOutput()));
        } finally {
            fclose($stream);
        }
    }

    public static function confirmationAnswers(): array
    {
        return [['', false], ['no', false], ['yes', true]];
    }

    public function testMissingGroupCannotReachMutation(): void
    {
        $setup = $this->createMock(EavSetup::class);
        $setup->expects($this->never())->method('removeAttributeGroup');
        $command = $this->getMockBuilder(DeleteCommand::class)
            ->onlyMethods(['initializeManagement', 'context', 'attributeGroup'])->getMock();
        $command->method('initializeManagement')->willReturn(true);
        $command->method('context')->willReturn([$setup, 4, 8]);
        $command->method('attributeGroup')->willThrowException(new \InvalidArgumentException('Attribute group was not found'));
        $input = new ArrayInput(['entityType' => 'catalog_product', 'attributeSetId' => '8', 'attributeGroupId' => '11', '--force' => true]);
        $input->bind($command->getDefinition());
        $method = new \ReflectionMethod($command, 'execute');
        $method->setAccessible(true);
        $this->expectException(\InvalidArgumentException::class);
        $method->invoke($command, $input, new BufferedOutput());
    }
}
