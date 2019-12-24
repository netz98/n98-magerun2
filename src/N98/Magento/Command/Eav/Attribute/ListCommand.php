<?php

namespace N98\Magento\Command\Eav\Attribute;

use Magento\Eav\Model\Attribute;
use Magento\Eav\Model\Entity\Type as EntityType;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection as AttributeCollection;
use N98\Magento\Command\AbstractMagentoCommand;
use N98\Util\Console\Helper\Table\Renderer\RendererFactory;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ListCommand
 * @package N98\Magento\Command\Eav\Attribute
 */
class ListCommand extends AbstractMagentoCommand
{
    /**
     * @var AttributeCollection
     */
    private $attributeCollection;

    /**
     * @param AttributeCollection $attributeCollection
     * @return void
     */
    public function inject(
        AttributeCollection $attributeCollection
    ) {
        $this->attributeCollection = $attributeCollection;
    }

    /**
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('eav:attribute:list')
            ->addOption(
                'add-source',
                null,
                InputOption::VALUE_NONE,
                'Add source models to list'
            )
            ->addOption(
                'add-backend',
                null,
                InputOption::VALUE_NONE,
                'Add backend type to list'
            )
            ->addOption(
                'filter-type',
                null,
                InputOption::VALUE_OPTIONAL,
                'Filter attributes by entity type'
            )
            ->addOption(
                'format',
                null,
                InputOption::VALUE_OPTIONAL,
                'Output Format. One of [' . implode(',', RendererFactory::getFormats()) . ']'
            )
            ->setDescription('List EAV attributes');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectMagento($output);
        if (!$this->initMagento()) {
            return;
        }

        $table = [];
        $addSource = $input->getOption('add-source');
        $addBackend = $input->getOption('add-backend');
        $filterType = $input->getOption('filter-type');
        $this->attributeCollection->setOrder('attribute_code', 'asc');

        /** @var Attribute $attribute */
        foreach ($this->attributeCollection as $attribute) {
            /** @var EntityType $entityType */
            $entityType = $attribute->getEntityType();
            if ($filterType &&
                $entityType->getEntityTypeCode() !== $filterType) {
                continue;
            }

            $row = [
                $attribute->getAttributeCode(),
                $attribute->getId(),
                $entityType->getEntityTypeCode() . ' (#' . $entityType->getEntityTypeId() . ')',
                $attribute->getFrontendLabel(),
            ];
            if ($addBackend) {
                $row[] = $attribute->getBackendType();
            }
            if ($addSource) {
                $row[] = $attribute->getSourceModel() ? $attribute->getSourceModel() : '';
            }

            $table[] = $row;
        }

        $headers = [
            'code',
            'id',
            'entity_type',
            'label',
        ];
        if ($addBackend) {
            $headers[] = 'backend_type';
        }
        if ($addSource) {
            $headers[] = 'source';
        }

        $this->getHelper('table')
            ->setHeaders($headers)
            ->renderByFormat($output, $table, $input->getOption('format'));
    }
}
