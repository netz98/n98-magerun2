<?php

namespace N98\Magento\Command\Eav\Attribute;

use Magento\Eav\Model\Config as EavConfig;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use N98\Magento\Command\AbstractMagentoCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RemoveCommand extends AbstractMagentoCommand
{
    /**
     * @var EavConfig
     */
    private $eavConfig;

    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @param EavConfig $eavConfig
     * @param EavSetupFactory $eavSetupFactory
     * @return void
     */
    public function inject(
        EavConfig $eavConfig,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->eavConfig = $eavConfig;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('eav:attribute:remove')
            ->addArgument(
                'entityType',
                InputArgument::REQUIRED,
                'Entity Type Code, e.g. catalog_product'
            )
            ->addArgument(
                'attributeCode',
                InputArgument::REQUIRED | InputArgument::IS_ARRAY,
                'Attribute Code'
            )
            ->setDescription('Remove attribute for a given attribute code');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectMagento($output);
        if (!$this->initMagento()) {
            return;
        }

        if ($this->runsInProductionMode($input, $output)) {
            $output->writeln('This command is not available in production mode');
            return;
        }

        $entityType = $input->getArgument('entityType');

        try {
            $existingAttributeCodes = $this->eavConfig->getEntityAttributeCodes($entityType);
        } catch (\Exception $e) {
            throw new \InvalidArgumentException($e->getMessage());
        }

        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create();
        foreach ($input->getArgument('attributeCode') as $attributeCode) {
            if (!in_array($attributeCode, $existingAttributeCodes)) {
                $output->writeln(sprintf(
                    '<comment>Attribute "%s" does not exist for entity type "%s", skipped</comment>',
                    $attributeCode,
                    $entityType
                ));
            } else {
                $eavSetup->removeAttribute($entityType, $attributeCode);
                $output->writeln(sprintf(
                    '<info>Successfully removed attribute "%s" from entity type "%s"</info>',
                    $attributeCode,
                    $entityType
                ));
            }
        }
    }
}
