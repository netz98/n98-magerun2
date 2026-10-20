<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Eav;

use Magento\Deploy\Model\Mode;
use Magento\Eav\Model\Config;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\State;
use N98\Magento\Command\TestCase;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Tester\CommandTester;

class InteractionTest extends TestCase
{
    /** @dataProvider completeInputs */
    public function testSuppliedInputsArePreservedWithoutPrompts(string $class, array $arguments): void
    {
        $this->assertNoInteraction($class, $arguments, true);
    }

    /** @dataProvider completeInputs */
    public function testNoninteractiveMissingInputsCannotSelectDefaults(string $class, array $arguments): void
    {
        $this->assertNoInteraction($class, [], false);
    }

    /** @dataProvider completeInputs */
    public function testMissingNoninteractiveArgumentsFailBeforeExecution(string $class, array $arguments): void
    {
        $command = $this->getMockBuilder($class)->onlyMethods(['execute'])->getMock();
        $command->expects($this->never())->method('execute');
        $this->getApplication()->add($command);
        $tester = new CommandTester($command);
        $tester->setInputs(['catalog_product', '42', '107', 'Must not be consumed']);
        $this->expectException(\Symfony\Component\Console\Exception\RuntimeException::class);
        $this->expectExceptionMessage('Not enough arguments');
        $tester->execute([], ['interactive' => false]);
    }

    private function assertNoInteraction(string $class, array $arguments, bool $interactive): void
    {
        $command = $this->getMockBuilder($class)->onlyMethods(['initializeManagement'])->getMock();
        $command->expects($this->never())->method('initializeManagement');
        $question = $this->createMock(QuestionHelper::class);
        $question->expects($this->never())->method('ask');
        $command->setHelperSet(new HelperSet(['question' => $question]));
        $input = new ArrayInput($arguments);
        $input->bind($command->getDefinition());
        $input->setInteractive($interactive);
        $before = [$input->getArguments(), $input->getOptions()];
        $method = new \ReflectionMethod($command, 'interact');
        $method->invoke($command, $input, new BufferedOutput());
        $this->assertSame($before, [$input->getArguments(), $input->getOptions()]);
    }

    public static function completeInputs(): array
    {
        return [
            [Attribute\CreateCommand::class, ['entityType' => 'catalog_product', 'attributeCode' => 'example', '--label' => 'Supplied label']],
            [AttributeGroup\CreateCommand::class, ['entityType' => 'catalog_product', 'attributeSetId' => '42', 'name' => 'Supplied group']],
            [AttributeGroup\ListCommand::class, ['entityType' => 'catalog_product', 'attributeSetId' => '42']],
            [AttributeGroup\ViewCommand::class, ['entityType' => 'catalog_product', 'attributeSetId' => '42', 'attributeGroupId' => '107']],
            [AttributeGroup\DeleteCommand::class, ['entityType' => 'catalog_product', 'attributeSetId' => '42', 'attributeGroupId' => '107']],
        ];
    }

    /** @dataProvider emptyChoices */
    public function testEmptyChoicesFailWithoutPromptOrMutation(string $class, array $arguments, string $context): void
    {
        $this->getApplication();
        $command = $this->getMockBuilder($class)
            ->onlyMethods(['initializeManagement', 'entityChoices', 'setChoices', 'groupRows', 'entityId', 'attributeSet'])->getMock();
        $command->method('initializeManagement')->willReturn(true);
        $command->method('entityChoices')->willReturn([]);
        $command->method('setChoices')->with(4)->willReturn([]);
        $command->method('groupRows')->with(42)->willReturn([]);
        $command->method('entityId')->willReturn(4);
        $command->method('attributeSet')->willReturn(['attribute_set_id' => 42]);
        $setup = $this->createMock(EavSetup::class);
        $setup->expects($this->never())->method('addAttribute');
        $setup->expects($this->never())->method('addAttributeGroup');
        $setup->expects($this->never())->method('removeAttributeGroup');
        $factory = $this->createMock(EavSetupFactory::class);
        $factory->method('create')->willReturn($setup);
        $resource = $this->createMock(ResourceConnection::class);
        $resource->expects($this->never())->method('getConnection');
        $command->inject($this->createMock(Config::class), $factory, $resource);
        $question = $this->createMock(QuestionHelper::class);
        $question->expects($this->never())->method('ask');
        $command->setHelperSet(new HelperSet(['question' => $question]));
        // Invoke only interaction: no injection, execution or writes can occur.
        $input = new ArrayInput($arguments);
        $input->bind($command->getDefinition());
        $method = new \ReflectionMethod($command, 'interact');
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('No choices available for ' . $context);
        $method->invoke($command, $input, new BufferedOutput());
    }

    public static function emptyChoices(): array
    {
        return [
            [AttributeGroup\ListCommand::class, [], 'entity type'],
            [AttributeGroup\ListCommand::class, ['entityType' => 'catalog_product'], 'attribute set'],
            [AttributeGroup\ViewCommand::class, ['entityType' => 'catalog_product', 'attributeSetId' => '42'], 'attribute group'],
        ];
    }

    /** @dataProvider abortedInputs */
    public function testCancelledOrInvalidContextDoesNotReachCreation(array $answers, array $arguments, string $message): void
    {
        $tester = $this->tester('eav:attribute-group:create', $answers);
        try {
            $tester->execute($arguments, ['interactive' => true]);
        } catch (\Exception $exception) {
            $this->assertStringContainsString($message, $exception->getMessage());
            $this->assertStringNotContainsString('Created attribute group', $tester->getDisplay());
            return;
        }
        $this->fail('Expected input collection to abort.');
    }

    public static function abortedInputs(): array
    {
        return [
            'empty answer cancels' => [[''], [], 'cancelled'],
            'EOF cancels' => [[], [], 'cancelled'],
            'explicit cancellation' => [['cancel'], [], 'cancelled'],
            'invalid supplied entity is not replaced' => [[], ['entityType' => 'n98_missing_entity'], 'Invalid entity_type'],
            'invalid supplied set is not replaced' => [[], ['entityType' => 'catalog_product', 'attributeSetId' => '2147483647'], 'not found'],
            'index is not a set ID' => [['0'], ['entityType' => 'catalog_product'], 'invalid'],
        ];
    }

    public function testNoninteractiveMissingLabelFailsWithoutCreatingAttribute(): void
    {
        $this->skipProductionMutations();
        $code = 'n98_test_' . bin2hex(random_bytes(5));
        $tester = $this->tester('eav:attribute:create', ['Must not be consumed']);
        try {
            $tester->execute(['entityType' => 'catalog_product', 'attributeCode' => $code], ['interactive' => false]);
            $this->fail('Expected missing --label to fail.');
        } catch (\InvalidArgumentException $exception) {
            $this->assertStringContainsString('Missing required option --label', $exception->getMessage());
        }
        $config = $this->getApplication()->getObjectManager()->get(Config::class);
        $config->clear();
        $this->assertFalse((bool) $config->getAttribute('catalog_product', $code)->getId());
        $this->assertStringNotContainsString('Admin label (required', $tester->getDisplay());
    }

    public function testOnlyMissingLabelIsPromptedAndMetadataIsPreserved(): void
    {
        $this->skipProductionMutations();
        $om = $this->getApplication()->getObjectManager();
        $setup = $om->get(EavSetupFactory::class)->create();
        $code = 'n98_test_' . bin2hex(random_bytes(5));
        try {
            $tester = $this->tester('eav:attribute:create', ['Prompted label']);
            $this->assertSame(0, $tester->execute([
                'entityType' => 'catalog_product', 'attributeCode' => $code,
                '--input' => 'boolean', '--default' => '0',
            ], ['interactive' => true]));
            $config = $om->get(Config::class);
            $config->clear();
            $attribute = $config->getAttribute('catalog_product', $code);
            $this->assertSame('Prompted label', $attribute->getFrontendLabel());
            $this->assertSame('boolean', $attribute->getFrontendInput());
            $this->assertSame('0', $attribute->getDefaultValue());
            $this->assertStringNotContainsString('Entity type (enter', $tester->getDisplay());
            $this->assertStringNotContainsString('Attribute code (required', $tester->getDisplay());
        } finally {
            $setup->removeAttribute('catalog_product', $code);
            $setup->cleanCache();
            $om->get(Config::class)->clear();
        }
    }

    /** @dataProvider invalidTextInputs */
    public function testInvalidOrMissingTextCannotCreateAttribute(array $answers): void
    {
        $tester = $this->tester('eav:attribute:create', $answers);
        try {
            $tester->execute(['entityType' => 'catalog_product', '--label' => 'Supplied'], ['interactive' => true]);
            $this->fail('Expected invalid or missing attribute code to abort.');
        } catch (\RuntimeException | \InvalidArgumentException $exception) {
            $this->assertNotSame('', $exception->getMessage());
        }
        $this->assertStringNotContainsString('Created attribute', $tester->getDisplay());
    }

    public static function invalidTextInputs(): array
    {
        return [[['invalid-code']], [['']], [[]]];
    }

    public function testInteractiveProductLifecycleWithPartialInputs(): void
    {
        $this->skipProductionMutations();
        $om = $this->getApplication()->getObjectManager();
        $setup = $om->get(EavSetupFactory::class)->create();
        $suffix = bin2hex(random_bytes(5));
        $setName = 'Magerun interactive ' . $suffix;
        $groupName = 'Interactive group ' . $suffix;
        $code = 'n98_test_' . $suffix;
        $setup->addAttributeSet('catalog_product', $setName);
        $setId = (string) $setup->getAttributeSetId('catalog_product', $setName);
        try {
            $create = $this->tester('eav:attribute-group:create', ['catalog_product', $setId, $groupName]);
            $this->assertSame(0, $create->execute([], ['interactive' => true]));
            $groupId = (string) $setup->getAttributeGroupByCode('catalog_product', $setId, $setup->convertToAttributeGroupCode($groupName), 'attribute_group_id');
            $this->assertGreaterThan(0, (int) $groupId);
            $this->assertStringNotContainsString('Sort order', $create->getDisplay());

            $attribute = $this->tester('eav:attribute:create', ['catalog_product', $code, 'Interactive label']);
            $this->assertSame(0, $attribute->execute(['--attribute-set' => $setId, '--attribute-group' => $groupId], ['interactive' => true]));
            $this->assertStringNotContainsString('Scope', $attribute->getDisplay());
            $this->assertStringNotContainsString('Sort order', $attribute->getDisplay());

            $list = $this->tester('eav:attribute-group:list', [$setId]);
            $this->assertSame(0, $list->execute(['entityType' => 'catalog_product'], ['interactive' => true]));
            $this->assertStringContainsString($groupName, $list->getDisplay());
            $this->assertStringNotContainsString('Entity type (enter', $list->getDisplay());

            $context = ['entityType' => 'catalog_product', 'attributeSetId' => $setId];
            $view = $this->tester('eav:attribute-group:view', [$groupId]);
            $this->assertSame(0, $view->execute($context, ['interactive' => true]));
            $this->assertStringContainsString($code, $view->getDisplay());
            $this->assertStringNotContainsString('Attribute set (enter', $view->getDisplay());
            $setup->removeAttribute('catalog_product', $code);
            $setup->cleanCache();

            $cancel = $this->tester('eav:attribute-group:delete', ['catalog_product', $setId, $groupId, 'no']);
            $this->assertSame(1, $cancel->execute([], ['interactive' => true]));
            $this->assertStringContainsString('Deletion cancelled', $cancel->getDisplay());
            $delete = $this->tester('eav:attribute-group:delete', [$groupId, 'yes']);
            $this->assertSame(0, $delete->execute($context, ['interactive' => true]));
        } finally {
            $setup->removeAttribute('catalog_product', $code);
            $setup->removeAttributeSet('catalog_product', $setId);
            $setup->cleanCache();
            $om->get(Config::class)->clear();
        }
    }

    private function tester(string $command, array $answers): CommandTester
    {
        $tester = new CommandTester($this->getApplication()->find($command));
        $tester->setInputs($answers);
        return $tester;
    }

    private function skipProductionMutations(): void
    {
        $mode = $this->getApplication()->getObjectManager()->create(Mode::class, [
            'input' => new ArgvInput(), 'output' => new ConsoleOutput(),
        ]);
        if ($mode->getMode() === State::MODE_PRODUCTION) {
            $this->markTestSkipped('EAV mutations are disabled in production mode.');
        }
    }
}
