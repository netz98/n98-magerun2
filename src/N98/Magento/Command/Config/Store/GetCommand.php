<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Config\Store;

use Magento\Config\App\Config\Type\System\Reader;
use Magento\Store\Model\Config\Processor\Placeholder;
use N98\Magento\Command\Config\AbstractConfigCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use UnexpectedValueException;

class GetCommand extends AbstractConfigCommand
{
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
            ->setDescription('Get effective store configuration values')
            ->setHelp(
                <<<EOT
                If <info>path</info> is not set, all available config items will be listed.
The <info>path</info> may contain wildcards (*, %, _).
If <info>path</info> ends with a trailing slash, all child items will be listed. E.g.

    config:store:get web/
is the same as
    config:store:get web/*

Values use Magento's native precedence and scope inheritance, including XML defaults,
config.php and env.php. Without --scope, list merged default paths and explicitly
configured website/store paths only. With --scope, include inherited paths.
Updated At is blank because effective configuration has no merged modification time.
Script exports contain effective values, not only database overrides.
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
            ->addFormatOption();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $searchPath = (string) $input->getArgument('path');
        if (substr($searchPath, -1) === '/') {
            $searchPath .= '*';
        }
        $scope = $input->getOption('scope');
        if ($scope !== null && $scope !== '') {
            $this->_validateScopeParam($scope);
        }
        $scopeId = $input->getOption('scope-id');
        if ($scope !== null && $scopeId !== null && $scopeId !== '') {
            $scopeId = $this->_convertScopeIdParam($scope, $scopeId);
        }
        $table = $this->getEffectiveRows($scope, $scopeId, $searchPath);
        if (!$table) {
            $output->writeln(sprintf("Couldn't find a config value for \"%s\"", $input->getArgument('path')));

            return Command::FAILURE;
        }

        foreach ($table as &$row) {
            $row['value'] = $this->_formatValue($row['value'], $input->getOption('decrypt') ? 'decrypt' : '');
        }
        unset($row);

        if ($input->hasParameterOption('--update-script')) {
            $this->renderAsUpdateScript($output, $table);
        } elseif ($input->hasParameterOption('--magerun-script')) {
            $this->renderAsMagerunScript($output, $table, $input->getOption('decrypt'));
        } else {
            $this->renderAsTable($output, $table, $input->getOption('format'));
        }

        return Command::SUCCESS;
    }

    /**
     * Only use source data to discover explicit paths. Magento resolves all values,
     * including deployment overrides, placeholders and scope inheritance.
     */
    protected function getExplicitConfiguration()
    {
        return $this->getConfigurationSource()->get();
    }

    protected function getConfigurationSource()
    {
        return $this->getObjectManager()->get('systemConfigSourceAggregated');
    }

    protected function getEffectiveConfiguration()
    {
        // Read fresh source data without invalidating or warming the application's
        // shared config cache. Keep backend values raw: metadata processors can
        // decrypt secrets, which this command must only do with --decrypt.
        return $this->getObjectManager()->get(Placeholder::class)->process(
            $this->getObjectManager()->create(Reader::class, ['source' => $this->getConfigurationSource()])->read()
        );
    }

    protected function getEffectiveRows($scope, $scopeId, $searchPath)
    {
        $explicit = $this->getExplicitConfiguration();
        $configuration = $this->getEffectiveConfiguration();
        $contexts = [['default', 0, '']];
        foreach ($this->_getStoreManager()->getWebsites(true) as $website) {
            $contexts[] = ['websites', $website->getId(), $website->getCode()];
        }
        foreach ($this->_getStoreManager()->getStores(true) as $store) {
            $contexts[] = ['stores', $store->getId(), $store->getCode()];
        }

        $pattern = '~^' . str_replace(['\\*', '%', '_'], ['.*', '.*', '.'], preg_quote($searchPath, '~')) . '$~iD';
        $rows = [];
        foreach ($contexts as [$type, $id, $code]) {
            if (($scope !== null && $scope !== '' && $scope !== $type)
                || ($scopeId !== null && $scopeId !== '' && (string) $scopeId !== (string) $id)) {
                continue;
            }
            $effective = $this->flattenConfiguration(
                $type === 'default' ? ($configuration['default'] ?? []) : ($configuration[$type][$code] ?? [])
            );
            $paths = ($scope !== null && $scope !== '') || $type === 'default'
                ? $effective
                : $this->flattenConfiguration($explicit[$type][$code] ?? []);
            foreach ($paths as $path => $unused) {
                if (($searchPath !== '' && !preg_match($pattern, $path)) || !array_key_exists($path, $effective)) {
                    continue;
                }
                $rows[] = [
                    'path' => $path,
                    'scope' => $type,
                    'scope_id' => $id,
                    'value' => $effective[$path],
                    'updated_at' => '',
                ];
            }
        }
        usort($rows, function ($left, $right) {
            return [$left['path'], array_search($left['scope'], $this->_scopes), (int) $left['scope_id']]
                <=> [$right['path'], array_search($right['scope'], $this->_scopes), (int) $right['scope_id']];
        });

        return $rows;
    }

    private function flattenConfiguration(array $configuration, $prefix = '')
    {
        $result = [];
        foreach ($configuration as $key => $value) {
            $path = $prefix . $key;
            if (is_array($value)) {
                $result += $this->flattenConfiguration($value, $path . '/');
            } else {
                $result[$path] = $value;
            }
        }

        return $result;
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
            ->setTitle('Config values')
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
     * @param bool $encrypt
     */
    protected function renderAsMagerunScript(OutputInterface $output, $table, $encrypt = false)
    {
        foreach ($table as $row) {
            $value = $row['value'];
            if ($value !== null) {
                $value = str_replace(["\n", "\r"], ['\n', '\r'], $value);
            }

            $disaplayValue = $value === null ? 'NULL' : escapeshellarg($value);
            $protectNullString = $value === 'NULL' ? '--no-null ' : '';
            $encryptOption = $encrypt && $value !== null ? '--encrypt ' : '';

            $line = sprintf(
                'config:store:set %s%s--scope-id=%s --scope=%s -- %s %s',
                $encryptOption,
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
