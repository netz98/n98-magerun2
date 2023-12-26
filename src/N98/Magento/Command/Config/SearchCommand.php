<?php
/**
 * @copyright Copyright (c) netz98 GmbH (https://www.netz98.de)
 *
 * @see PROJECT_LICENSE.txt
 */

declare(strict_types=1);

namespace N98\Magento\Command\Config;

use Magento\Backend\Model\Search\Config\Result\Builder;
use Magento\Backend\Model\Search\Config as ConfigSeaerch;
use Magento\Config\Model\Config\Structure as ConfigStructure;
use Magento\Config\Model\Config\Structure\Data as ConfigStructureData;
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
    private Builder $resultBuilder;

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

        $appState = $this->getObjectManager()->get(State::class);
        $appState->emulateAreaCode('adminhtml', function () use ($input, $output) {
            $results = $this->getObjectManager()->create(ConfigSeaerch::class)
                ->setStart(0)
                ->setLimit(100)
                ->setQuery($input->getArgument('text'))
                ->load()
                ->getResults();

            var_dump($results);
            die;
        });

        $this->configStructure = $this->getObjectManager()->create(ConfigStructure::class);
        $tabs = $this->configStructure->getTabs();

        die;

        if ($tabs instanceof \Iterator) {
            $tabs = iterator_to_array($tabs);
        }
        var_dump($tabs);
        die;
        $this->configStructureData = $this->getObjectManager()->create(ConfigStructureData::class);
        $this->resultBuilder = $this->getObjectManager()->create(Builder::class);

        $configData = $this->configStructureData->get();
        if (isset($configData['sections'])) {
            $this->findInStructure(
                $configData['sections'],
                $input->getArgument('text')
            );
        }

        $results = $this->resultBuilder->getAll();

        if (empty($results)) {
            $output->writeln('<info>No results found.</info>');
            return self::SUCCESS;
        }

        $tableData = [];
        foreach ($results as $result) {
            $tableData[] = [
                $result['name'],
                $result['description'],
            ];
        }

        $this->getHelper('table')
            ->setHeaders(['Name', 'Description'])
            ->renderByFormat($output, $tableData, $input->getOption('format'));

        return self::SUCCESS;
    }

    /**
     * @param array $elements
     * @param string $searchTerm
     */
    private function findInStructure($elements, $searchTerm)
    {
        if (empty($searchTerm)) {
            return;
        }

        foreach ($elements as $elementData) {
            // search in label
            if (isset($elementData['label']) && mb_stripos((string)$elementData['label'], $searchTerm) !== false) {
                // if element has a path, add it to the result
                if (isset($elementData['path'])) {
                    $element = $this->configStructure->getElementByConfigPath($elementData['path']);
                    $this->resultBuilder->add($element, $elementData['path']);
                }
            }

            // If the element has children (like groups), recurse
            if (isset($elementData['children'])) {
                $this->findInStructure($elementData['children'], $searchTerm);
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
