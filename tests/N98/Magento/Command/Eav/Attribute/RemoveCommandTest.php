<?php

namespace N98\Magento\Command\Eav\Attribute;

use Magento\Eav\Model\Config as EavConfig;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use N98\Magento\Command\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class RemoveCommandTest extends TestCase
{
    /**
     * @var RemoveCommand;
     */
    protected $command;

    /**
     * @var CommandTester
     */
    protected $commandTester;

    /**
     * @return void
     */
    protected function setUp()
    {
        $application = $this->getApplication();
        $application->add(new RemoveCommand());

        $this->command = $this->getApplication()->find('eav:attribute:remove');
        $this->commandTester = new CommandTester($this->command);
    }

    /**
     * @param string $entityType
     * @param string $attributeCode
     * @return bool
     */
    protected function attributeExists($entityType, $attributeCode)
    {
        /** @var EavConfig $eavConfig */
        $eavConfig = $this->getApplication()->getObjectManager()->create(EavConfig::class);
        $existingAttributeCodes = $eavConfig->getEntityAttributeCodes($entityType);
        return in_array($attributeCode, $existingAttributeCodes);
    }

    /**
     * @return void
     */
    public function testThrowsExceptionOnNonExistingEntityType()
    {
        $this->setExpectedException(
            '\InvalidArgumentException',
            'Invalid entity_type specified: non_existing_entity_type'
        );
        $this->commandTester->execute([
            'command'       => $this->command->getName(),
            'entityType'    => 'non_existing_entity_type',
            'attributeCode' => ['whatever'],
        ]);
    }

    /**
     * @return void
     */
    public function testDetectsNonExistingAttributeCode()
    {
        $this->assertDisplayContains(
            [
                'command'       => $this->command->getName(),
                'entityType'    => 'customer',
                'attributeCode' => ['non-existing-attribute-code'],
            ],
            'Attribute "non-existing-attribute-code" does not exist for entity type "customer", skipped'
        );
    }

    /**
     * @return void
     */
    public function testRemovesAttributeSuccessfully()
    {
        $entityType = 'customer';
        $attributeCode = 'temporary_attribute';

        /** @var EavSetup $eavSetup */
        $eavSetup = $this->getApplication()->getObjectManager()
            ->get(EavSetupFactory::class)
            ->create();

        $eavSetup->addAttribute(
            $entityType,
            $attributeCode,
            [
                'type'  => 'text',
                'input' => 'text',
                'label' => 'Temporary Attribute',
            ]
        );
        $eavSetup->cleanCache();
        $this->assertTrue($this->attributeExists($entityType, $attributeCode));

        $this->commandTester->execute([
            'command'       => $this->command->getName(),
            'entityType'    => $entityType,
            'attributeCode' => [$attributeCode],
        ]);
        $eavSetup->cleanCache();
        $this->assertFalse($this->attributeExists($entityType, $attributeCode));
    }
}
