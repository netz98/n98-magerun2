<?php

declare(strict_types=1);

namespace N98\Magento\Command\Config;

use Magento\Config\Model\Config\Structure as ConfigStructure;
use Magento\Config\Model\Config\Structure\Data as ConfigStructureData;
use Magento\Config\Model\Config\Structure\Element\AbstractComposite;
use Magento\Framework\App\Area;
use Magento\Framework\App\AreaList;
use Magento\Framework\App\State;
use Magento\Framework\ObjectManager\ConfigLoaderInterface;
use N98\Magento\Command\AbstractMagentoCommand;
use N98\Util\Console\Helper\Table\Renderer\RendererFactory;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SearchCommand extends AbstractMagentoCommand
{
    private ConfigStructure $configStructure;
    private ConfigStructureData $configStructureData;
    private array $results = [];

    private $tabMap = [];

    protected function configure()
    {
        $this
            ->setName('config:search')
            ->setDescription('Search system configuration descriptions.')
            ->setHelp(
                <<<EOT
                Searches the merged system.xml configuration tree <labels/> and <comments/> for the indicated text.
EOT
            )
            ->addOption(
                'format',
                null,
                InputOption::VALUE_OPTIONAL,
                'Output Format. One of [' . implode(',', RendererFactory::getFormats()) . ']'
            )
            ->addArgument('text', InputArgument::REQUIRED, 'The text to search for');

        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int Non zero if invalid type, 0 otherwise
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectMagento($output);
        if (!$this->initMagento()) {
            return self::FAILURE;
        }

        $this->setAdminArea();

        // We cannot use the search objects from Magento_Backend modules because they are
        // using the ACL resource reader which is not available in the CLI context without
        // defining loading a admin user. So we load the data by the using the data layer below.

        $this->configStructure = $this->getObjectManager()->create(ConfigStructure::class);
        $this->configStructureData = $this->getObjectManager()->create(ConfigStructureData::class);

        $configData = $this->configStructureData->get();

        $this->tabMap = $configData['tabs'];

        if (isset($configData['sections'])) {
            $this->findInStructure(
                $configData['sections'],
                $input->getArgument('text'),
                ''
            );
        }

        if (count($this->results) === 0) {
            $output->writeln('<info>No results found.</info>');
            return self::SUCCESS;
        }

        $this->getHelper('table')
            ->setHeaders(array_keys($this->results[0]))
            ->renderByFormat($output, $this->results, $input->getOption('format'));

        return self::SUCCESS;
    }

    /**
     * @param array $elements
     * @param string $searchTerm
     * @param string $pathLabel
     */
    private function findInStructure($elements, $searchTerm, $pathLabel = '')
    {
        if (empty($searchTerm)) {
            return;
        }

        foreach ($elements as $structureElement) {

            // Initial call contains only the sections and need to extract the tabs
            if (is_array($structureElement)) {
                if (isset($structureElement['tab'])) {
                    $pathLabel =  $this->tabMap[$structureElement['tab']]['label'];
                }
                $structureElement = $this->configStructure->getElement($structureElement['id']);
            }

            if (mb_stripos((string)$structureElement->getLabel(), $searchTerm) !== false
                || mb_stripos((string)$structureElement->getComment(), $searchTerm) !== false
            ) {
                $elementData = $structureElement->getData();
                $this->results[] = [
                    'id' => trim($structureElement->getPath(), '/'),
                    'type' => $elementData['_elementType'],
                    'name' => trim($pathLabel . ' / ' . trim((string)$structureElement->getLabel()), '/'),
                ];
            }

            $elementPathLabel = $pathLabel . ' / ' . $structureElement->getLabel();
            if ($structureElement instanceof AbstractComposite && $structureElement->hasChildren()) {
                $this->findInStructure($structureElement->getChildren(), $searchTerm, $elementPathLabel);
            }
        }
    }

    /**
     * Required to avoid "Area code not set" exceptions from Mage framework
     */
    private function setAdminArea()
    {
        $appState = $this->getObjectManager()->get(State::class);
        $appState->setAreaCode(Area::AREA_ADMINHTML);
        $this->getObjectManager()->configure(
            $this->getObjectManager()
                ->get(ConfigLoaderInterface::class)
                ->load(Area::AREA_ADMINHTML)
        );

        $areaList = $this->getObjectManager()->get(AreaList::class);
        $areaList->getArea(Area::AREA_ADMINHTML)
            ->load(Area::PART_CONFIG)
            ->load(Area::PART_TRANSLATE);
    }
}
