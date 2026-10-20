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
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\State;
use N98\Magento\Command\TestCase;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Tester\CommandTester;

/** Requires an isolated, configured Magento installation; creates and removes its own fixtures. */
class ManagementCommandTest extends TestCase
{
    private function executeCommand(string $name, array $arguments): CommandTester
    {
        $tester = new CommandTester($this->getApplication()->find($name));
        $tester->execute($arguments, ['interactive' => false]);
        return $tester;
    }

    public function testProductAttributeAndGroupLifecycle(): void
    {
        $om = $this->getApplication()->getObjectManager();
        $mode = $om->create(Mode::class, ['input' => new ArgvInput(), 'output' => new ConsoleOutput()]);
        if ($mode->getMode() === State::MODE_PRODUCTION) {
            $this->markTestSkipped('EAV mutations are disabled in production mode.');
        }
        $setup = $om->get(EavSetupFactory::class)->create();
        $config = $om->get(Config::class);
        $suffix = bin2hex(random_bytes(5));
        $setName = 'Magerun test ' . $suffix;
        $groupName = 'Magerun group ' . $suffix;
        $code = 'n98_test_' . $suffix;
        $entity = 'catalog_product';
        $setup->addAttributeSet($entity, $setName);
        $setId = (int) $setup->getAttributeSetId($entity, $setName);
        $context = ['entityType' => $entity, 'attributeSetId' => (string) $setId];
        try {
            $this->assertRejected('eav:attribute-group:create', $context + ['name' => '123'], 'non-numeric');
            $created = $this->executeCommand('eav:attribute-group:create', $context + ['name' => $groupName, '--sort-order' => '99']);
            $this->assertSame(0, $created->getStatusCode());
            $groupId = (int) $setup->getAttributeGroupByCode($entity, $setId, $setup->convertToAttributeGroupCode($groupName), 'attribute_group_id');
            $this->assertGreaterThan(0, $groupId);
            $groupContext = $context + ['attributeGroupId' => (string) $groupId];

            $list = $this->executeCommand('eav:attribute-group:list', $context);
            $this->assertSame(0, $list->getStatusCode());
            $this->assertStringContainsString($groupName, $list->getDisplay());
            $cancelled = $this->executeCommand('eav:attribute-group:delete', $groupContext);
            $this->assertSame(1, $cancelled->getStatusCode());
            $this->assertNotEmpty($setup->getAttributeGroup($entity, $setId, $groupId));

            $attributeArgs = [
                'entityType' => $entity, 'attributeCode' => $code, '--label' => 'Test label',
                '--input' => 'select', '--option' => ['Red', 'Green'], '--required' => '0',
                '--attribute-set' => (string) $setId, '--attribute-group' => (string) $groupId,
            ];
            $attribute = $this->executeCommand('eav:attribute:create', $attributeArgs);
            $this->assertSame(0, $attribute->getStatusCode());
            $config->clear();
            $model = $config->getAttribute($entity, $code);
            $this->assertGreaterThan(0, (int) $model->getId());
            $this->assertSame('int', $model->getBackendType());
            $this->assertSame('select', $model->getFrontendInput());
            $this->assertSame('Test label', $model->getFrontendLabel());
            $this->assertTrue((bool) $model->getIsUserDefined());
            $labels = array_column($model->getSource()->getAllOptions(false), 'label');
            $this->assertContains('Red', $labels);
            $this->assertContains('Green', $labels);

            $view = $this->executeCommand('eav:attribute-group:view', $groupContext);
            $this->assertSame(0, $view->getStatusCode());
            $this->assertStringContainsString($code, $view->getDisplay());
            $this->assertStringContainsString($groupName, $view->getDisplay());
            $this->assertRejected('eav:attribute:create', $attributeArgs, 'already exists');
            $this->assertRejected('eav:attribute-group:create', $context + ['name' => $groupName], 'already exists');
            $this->assertRejected('eav:attribute-group:delete', $groupContext + ['--force' => true], 'populated');
            $defaultId = $setup->getDefaultAttributeGroupId($entity, $setId);
            $this->assertRejected('eav:attribute-group:delete', $context + ['attributeGroupId' => (string) $defaultId, '--force' => true], 'default');
            $this->assertRejected('eav:attribute-group:view', $context + ['attributeGroupId' => '2147483647'], 'not found');
            $this->assertRejected('eav:attribute-group:list', ['entityType' => 'customer', 'attributeSetId' => (string) $setId], 'not found');

            // A missing assignment target must not leave a newly-created attribute behind.
            $invalidArgs = array_replace($attributeArgs, ['attributeCode' => $code . 'x', '--attribute-group' => '2147483647']);
            $this->assertRejected('eav:attribute:create', $invalidArgs, 'not found');
            $config->clear();
            $this->assertFalse((bool) $config->getAttribute($entity, $code . 'x')->getId());

            $setup->removeAttribute($entity, $code);
            $setup->cleanCache();
            $deleted = $this->executeCommand('eav:attribute-group:delete', $groupContext + ['--force' => true]);
            $this->assertSame(0, $deleted->getStatusCode());
            $resource = $om->get(ResourceConnection::class);
            $connection = $resource->getConnection();
            $this->assertEmpty($connection->fetchOne($connection->select()
                ->from($resource->getTableName('eav_attribute_group'), 'attribute_group_id')
                ->where('attribute_group_id = ?', $groupId)));
        } finally {
            $setup->removeAttribute($entity, $code);
            $setup->removeAttribute($entity, $code . 'x');
            $setup->removeAttributeSet($entity, $setId);
            $setup->cleanCache();
            $config->clear();
        }
    }

    public function testCustomerAndAddressAttributeCreation(): void
    {
        $om = $this->getApplication()->getObjectManager();
        $mode = $om->create(Mode::class, ['input' => new ArgvInput(), 'output' => new ConsoleOutput()]);
        if ($mode->getMode() === State::MODE_PRODUCTION) {
            $this->markTestSkipped('EAV mutations are disabled in production mode.');
        }
        $setup = $om->get(EavSetupFactory::class)->create();
        $config = $om->get(Config::class);
        $code = 'n98_test_' . bin2hex(random_bytes(5));
        foreach (['customer', 'customer_address'] as $entity) {
            try {
                $created = $this->executeCommand('eav:attribute:create', [
                    'entityType' => $entity, 'attributeCode' => $code, '--label' => 'Customer test',
                    '--input' => 'text', '--required' => '0', '--default' => 'Example',
                ]);
                $this->assertSame(0, $created->getStatusCode());
                $config->clear();
                $attribute = $config->getAttribute($entity, $code);
                $this->assertGreaterThan(0, (int) $attribute->getId());
                $this->assertSame('varchar', $attribute->getBackendType());
                $this->assertSame('Example', $attribute->getDefaultValue());
                $this->assertFalse((bool) $attribute->getIsRequired());
                $this->assertTrue((bool) $attribute->getIsUserDefined());
            } finally {
                $setup->removeAttribute($entity, $code);
                $setup->cleanCache();
                $config->clear();
            }
        }
    }

    private function assertRejected(string $name, array $arguments, string $message): void
    {
        try {
            $tester = $this->executeCommand($name, $arguments);
        } catch (\InvalidArgumentException $exception) {
            $this->assertStringContainsString($message, $exception->getMessage());
            return;
        }
        $this->assertNotSame(0, $tester->getStatusCode());
        $this->assertStringContainsString($message, $tester->getDisplay());
    }
}
