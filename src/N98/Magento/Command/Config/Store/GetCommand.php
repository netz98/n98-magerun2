<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Config\Store;

use Magento\Config\Model\ResourceModel\Config\Data\Collection;
use Magento\Framework\App\Config\ScopeConfigInterface;
use N98\Magento\Command\Config\AbstractConfigCommand;
use N98\Util\Console\Helper\Table\Renderer\RendererFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use UnexpectedValueException;

class GetCommand extends AbstractConfigCommand
{
    use ConfigReaderTrait;

    /**
     * @var Collection
     */
    private $collection;

    /**
     * @var array
     */
    protected $_scopes = [
        'default',
        'websites',
        'stores'
    ];

    protected function configure()
    {
        $this
            ->setName('config:store:get')
            ->setDescription('Get a store config item')
            ->setHelp(
                <<<EOT
                If <info>path</info> is not set, all available config items will be listed.
The <info>path</info> may contain wildcards (*).
If <info>path</info> ends with a trailing slash, all child items will be listed. E.g.

    config:store:get web/
is the same as
    config:store:get web/*
EOT
            )
            ->addArgument('path', InputArgument::OPTIONAL, 'The config path')
            ->addOption(
                'scope',
                null,
                InputOption::VALUE_REQUIRED,
                'The config value\'s scope (default, websites, stores)'
            )
            ->addOption('scope-id', null, InputOption::VALUE_REQUIRED, 'The config value\'s scope ID or scope code.')
            ->addOption(
                'decrypt',
                null,
                InputOption::VALUE_NONE,
                'Decrypt the config value using env.php\'s crypt key'
            )
            ->addOption('update-script', null, InputOption::VALUE_NONE, 'Output as update script lines')
            ->addOption('magerun-script', null, InputOption::VALUE_NONE, 'Output for usage with config:store:set')
            ->addOption(
                'format',
                null,
                InputOption::VALUE_OPTIONAL,
                'Output Format. One of [' . implode(',', RendererFactory::getFormats()) . ']'
            );

        $help = <<<HELP
If path is not set, all available config items will be listed. path may contain wildcards (*)
HELP;
        $this->setHelp($help);
    }

    /**
     * @param Collection $collection
     */
    public function inject(Collection $collection)
    {
        $this->collection = $collection;
    }

    /**
     * Maps database scope names to ScopeConfigInterface scope names
     *
     * @param string $databaseScope
     * @return string
     */
    private function mapDatabaseScopeToConfigScope($databaseScope)
    {
        switch ($databaseScope) {
            case 'websites':
                return 'website';
            case 'stores':
                return 'store';
            case 'default':
            default:
                return 'default';
        }
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $table = [];
        $collection = $this->collection;

        $searchPath = $input->getArgument('path');

        if (substr($input->getArgument('path'), -1, 1) === '/') {
            $searchPath .= '*';
        }

        $collection->addFieldToFilter('path', [
            'like' => str_replace('*', '%', $searchPath),
        ]);

        $scope = $input->getOption('scope');
        if ($input->hasParameterOption('--scope') && $scope !== null && $scope !== false && $scope !== '') {
            $this->_validateScopeParam($scope);
            $collection->addFieldToFilter('scope', ['eq' => $scope]);
        }

        $scopeId = $input->getOption('scope-id');
        if ($input->hasParameterOption('--scope-id') && $scopeId !== null && $scopeId !== false && $scopeId !== '') {
            if ($scope !== null) {
                $scopeId = $this->_convertScopeIdParam($scope, $scopeId);
            }
            $collection->addFieldToFilter(
                'scope_id',
                ['eq' => $scopeId]
            );
        }

        $collection->addOrder('path', 'ASC');

        // sort according to the config overwrite order
        // trick to force order default -> (f)website -> store , because f comes after d and before s
        $collection->addOrder('REPLACE(scope, "website", "fwebsite")', 'ASC');

        $collection->addOrder('scope_id', 'ASC');

        if ($collection->count() == 0) {
            $output->writeln(sprintf("Couldn't find a config value for \"%s\"", $input->getArgument('path')));

            return Command::FAILURE;
        }

        foreach ($collection as $item) {
            // Use ScopeConfigInterface to get the actual effective value
            $configScope = $this->mapDatabaseScopeToConfigScope($item->getScope());
            $actualValue = $this->getScopeConfigValue(
                $item->getPath(),
                $configScope,
                $item->getScopeId()
            );

            $table[] = [
                'path'     => $item->getPath(),
                'scope'    => $item->getScope(),
                'scope_id' => $item->getScopeId(),
                'value'    => $this->_formatValue(
                    $actualValue,
                    $input->getOption('decrypt') ? 'decrypt' : ''
                ),
                'updated_at' => $item->getUpdatedAt()
            ];
        }

        ksort($table);

        if ($input->hasParameterOption('--update-script')) {
            $this->renderAsUpdateScript($output, $table);
        } elseif ($input->hasParameterOption('--magerun-script')) {
            $this->renderAsMagerunScript($output, $table);
        } else {
            $this->renderAsTable($output, $table, $input->getOption('format'));
        }

        return Command::SUCCESS;
    }

    /**
     * @param OutputInterface $output
     * @param array $table
     * @param string $format
     */
    protected function renderAsTable(OutputInterface $output, $table, $format)
    {
        $formattedTable = [];
        foreach ($table as $row) {
            $formattedTable[] = [
                $row['path'],
                $row['scope'],
                $row['scope_id'],
                $this->renderTableValue($row['value'], $format),
                $row['updated_at']
            ];
        }

        /* @var $tableHelper \N98\Util\Console\Helper\TableHelper */
        $tableHelper = $this->getHelper('table');
        $tableHelper
            ->setHeaders(['Path', 'Scope', 'Scope-ID', 'Value', 'Updated At'])
            ->setRows($formattedTable)
            ->renderByFormat($output, $formattedTable, $format);
    }

    private function renderTableValue($value, $format)
    {
        if ($value === null || $value === 'NULL') {
            switch ($format) {
                case null:
                    $value = self::DISPLAY_NULL_UNKNOWN_VALUE;
                    break;
                case 'json':
                    break;
                case 'csv':
                case 'xml':
                    $value = 'NULL';
                    break;
                default:
                    throw new UnexpectedValueException(
                        sprintf('Unhandled format %s', var_export($value, true))
                    );
            }
        }

        return $value;
    }

    /**
     * @param OutputInterface $output
     * @param array $table
     */
    protected function renderAsUpdateScript(OutputInterface $output, $table)
    {
        $output->writeln('<?php');
        $output->writeln('$installer = $this;');
        $output->writeln('# generated by n98-magerun');

        foreach ($table as $row) {
            if ($row['scope'] == 'default') {
                $output->writeln(
                    sprintf(
                        '$installer->setConfigData(%s, %s);',
                        var_export($row['path'], true),
                        var_export($row['value'], true)
                    )
                );
            } else {
                $output->writeln(
                    sprintf(
                        '$installer->setConfigData(%s, %s, %s, %s);',
                        var_export($row['path'], true),
                        var_export($row['value'], true),
                        var_export($row['scope'], true),
                        var_export($row['scope_id'], true)
                    )
                );
            }
        }
    }

    /**
     * @param OutputInterface $output
     * @param array $table
     */
    protected function renderAsMagerunScript(OutputInterface $output, $table)
    {
        foreach ($table as $row) {
            $value = $row['value'];
            if ($value !== null) {
                $value = str_replace(["\n", "\r"], ['\n', '\r'], $value);
            }

            $disaplayValue = $value === null ? 'NULL' : escapeshellarg($value);
            $protectNullString = $value === 'NULL' ? '--no-null ' : '';

            $line = sprintf(
                'config:store:set %s--scope-id=%s --scope=%s -- %s %s',
                $protectNullString,
                $row['scope_id'],
                $row['scope'],
                escapeshellarg($row['path']),
                $disaplayValue
            );
            $output->writeln($line);
        }
    }
}
