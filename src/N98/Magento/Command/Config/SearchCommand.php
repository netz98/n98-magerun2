<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace N98\Magento\Command\Config;

use function Laravel\Prompts\search;
use Magento\Config\Model\Config\Structure as ConfigStructure;
use Magento\Config\Model\Config\Structure\Data as ConfigStructureData;
use Magento\Config\Model\Config\Structure\Element\AbstractComposite;
use Magento\Framework\App\Area;
use Magento\Framework\App\AreaList;
use Magento\Framework\App\State;
use Magento\Framework\ObjectManager\ConfigLoaderInterface;
use N98\Magento\Application\Console\CommandPalette;
use N98\Magento\Command\AbstractMagentoCommand;
use N98\Util\Console\Interaction;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

class SearchCommand extends AbstractMagentoCommand
{
    /**
     * Rows of the result list to keep on screen.
     */
    private const SCROLL = 12;

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
            ->addFormatOption()
            ->addArgument('text', InputArgument::OPTIONAL, 'The text to search for');

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

        $searchTerm = $input->getArgument('text');
        $interactive = $searchTerm === null;

        if ($interactive && !$this->canBrowse($input, $output)) {
            // Same message Symfony produced while the argument was still REQUIRED, so scripts that
            // relied on the failure keep seeing it rather than hanging on a prompt they cannot answer.
            throw new RuntimeException('Not enough arguments (missing: "text").');
        }

        if (isset($configData['sections'])) {
            // A null term collects the whole tree, which is what the picker searches through.
            $this->findInStructure(
                $configData['sections'],
                $searchTerm,
                ''
            );
        }

        if (count($this->results) === 0) {
            $output->writeln('<info>No results found.</info>');
            return self::SUCCESS;
        }

        if ($interactive) {
            return $this->browse($output);
        }

        $this->getHelper('table')
            ->setTitle('Config matches')
            ->setHeaders(array_keys($this->results[0]))
            ->renderByFormat($output, $this->results, $input->getOption('format'));

        return self::SUCCESS;
    }

    /**
     * Whether the interactive picker can be offered for this invocation.
     */
    private function canBrowse(InputInterface $input, OutputInterface $output): bool
    {
        // A caller that asked for json/csv/xml wants data, not a prompt.
        if ($input->getOption('format')) {
            return false;
        }

        return Interaction::isPromptable($input, $output);
    }

    /**
     * Let the user search the configuration tree as they type and pick a single entry.
     *
     * `config:search` exists to answer "where does this setting live?", and a term good enough to
     * narrow 3000-odd entries to a readable table is usually found by trial and error. Doing that
     * against a live list is faster than re-running the command with a different word each time.
     */
    private function browse(OutputInterface $output): int
    {
        // Keyed by config path, which is what search() hands back. It has to be a *non-numeric*
        // key: PHP turns numeric string keys back into integers, and laravel/prompts returns the
        // label rather than the key once the option array looks like a plain list.
        $byPath = [];
        foreach ($this->results as $result) {
            if ($result['id'] !== '') {
                $byPath[$result['id']] = $result;
            }
        }

        if ($byPath === []) {
            $output->writeln('<info>No results found.</info>');

            return self::SUCCESS;
        }

        try {
            $chosen = search(
                label: 'Which setting are you looking for?',
                options: fn (string $value): array => $this->match($byPath, $value),
                placeholder: 'Start typing, e.g. base url, robots, cache',
                scroll: self::SCROLL,
                hint: sprintf('%d settings available. Ctrl+C to quit.', count($byPath))
            );
        } catch (Throwable $e) {
            // Ctrl+C, and a terminal that turns out not to support raw input, both land here.
            CommandPalette::restoreTerminal();

            return self::SUCCESS;
        }

        CommandPalette::restoreTerminal();

        if (!is_string($chosen) || !isset($byPath[$chosen])) {
            return self::SUCCESS;
        }

        $result = $byPath[$chosen];

        $this->io->heading('Config match');
        $this->io->keyValue([
            'path' => $result['id'],
            'type' => $result['type'],
            'name' => self::plainLabel($result['name']),
        ]);
        $this->io->hint(sprintf('Read the current value with: config:store:get %s', $result['id']));

        return self::SUCCESS;
    }

    /**
     * Rank collected settings against what the user has typed.
     *
     * A path match outranks a label match, so typing "base_url" offers `web/unsecure/base_url`
     * before the settings that merely mention base URLs in their breadcrumb.
     *
     * @param array<string, array<string, string>> $byPath
     * @return array<string, string> config path => label shown in the list
     */
    private function match(array $byPath, string $value): array
    {
        $needle = trim(mb_strtolower($value));

        $pathMatches = [];
        $nameMatches = [];

        foreach ($byPath as $path => $result) {
            if ($needle === '' || str_contains(mb_strtolower($path), $needle)) {
                $pathMatches[$path] = self::optionLabel($path, $result);
                continue;
            }

            if (str_contains(mb_strtolower($result['name']), $needle)) {
                $nameMatches[$path] = self::optionLabel($path, $result);
            }
        }

        return $pathMatches + $nameMatches;
    }

    /**
     * @param array<string, string> $result
     */
    private static function optionLabel(string $path, array $result): string
    {
        return sprintf('%s  —  %s', self::plainLabel($result['name']), $path);
    }

    /**
     * Magento's system.xml labels are HTML fragments - "<strong>Get more insights</strong>" and
     * the like. laravel/prompts writes to the terminal directly rather than through Symfony's
     * formatter, so the markup would show up as-is in the picker.
     */
    private static function plainLabel(string $name): string
    {
        return trim(preg_replace('/\s+/u', ' ', strip_tags($name)));
    }

    /**
     * @param array $elements
     * @param string|null $searchTerm null collects every element, for the interactive picker
     * @param string $pathLabel
     */
    private function findInStructure($elements, $searchTerm, $pathLabel = '')
    {
        if ($searchTerm !== null && empty($searchTerm)) {
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

            if ($searchTerm === null
                || mb_stripos((string)$structureElement->getLabel(), $searchTerm) !== false
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
