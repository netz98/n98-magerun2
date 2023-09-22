<?php
/**
 * @copyright Copyright (c) netz98 GmbH (https://www.netz98.de)
 *
 * @see PROJECT_LICENSE.txt
 */

declare(strict_types=1);

namespace N98\Magento\Command\Config;

use Magento\Backend\Model\Search\Config\Result\Builder;
use Magento\Config\Model\Config\Structure as ConfigStructure;
use Magento\Config\Model\Config\Structure\Element\AbstractComposite;
use Magento\Config\Model\Config\Structure\Element\Iterator as ElementIterator;
use Magento\Framework\App\Area;
use Magento\Framework\App\AreaList;
use Magento\Framework\App\State;
use Magento\Framework\ObjectManager\ConfigLoaderInterface;
use N98\Magento\Command\AbstractMagentoCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SearchCommand extends AbstractMagentoCommand
{
    private ConfigStructure $configStructure;
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

        $this->configStructure = $this->getObjectManager()->create(ConfigStructure::class);
        $this->resultBuilder = $this->getObjectManager()->create(Builder::class);

        $this->findInStructure(
            $this->configStructure->getTabs(),
            $input->getArgument('text')
        );
        var_dump($this->resultBuilder->getAll());

        return self::SUCCESS;
    }

    /**
     * Copy of core logic from \Magento\Backend\Model\Search\Config::findInStructure
     *
     * @param ElementIterator $structureElementIterator
     * @param string $searchTerm
     * @param string $pathLabel
     * @return void
     * @see \Magento\Backend\Model\Search\Config::findInStructure
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    private function findInStructure(ElementIterator $structureElementIterator, $searchTerm, $pathLabel = '')
    {
        if (empty($searchTerm)) {
            return;
        }

        foreach ($structureElementIterator as $structureElement) {
            if (mb_stripos((string)$structureElement->getLabel(), $searchTerm) !== false) {
                $this->resultBuilder->add($structureElement, $pathLabel);
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
