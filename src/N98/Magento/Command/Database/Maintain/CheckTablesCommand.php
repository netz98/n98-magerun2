<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Database\Maintain;

use N98\Magento\Command\AbstractMagentoCommand;
use N98\Util\Console\Helper\Table\Renderer\RendererFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CheckTablesCommand
 * @package N98\Magento\Command\Database\Maintain
 */
class CheckTablesCommand extends AbstractMagentoCommand
{
    const MESSAGE_CHECK_NOT_SUPPORTED = 'The storage engine for the table doesn\'t support check';
    const MESSAGE_REPAIR_NOT_SUPPORTED = 'The storage engine for the table doesn\'t support repair';

    /**
     * @var \Symfony\Component\Console\Input\InputInterface
     */
    protected $input = null;

    /**
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    protected $output = null;

    /**
     * @var \N98\Util\Console\Helper\DatabaseHelper
     */
    protected $dbHelper = null;

    /**
     * @var bool
     */
    protected $showProgress = false;

    /**
     * @var array
     */
    protected $allowedTypes = [
        'QUICK',
        'FAST',
        'CHANGED',
        'MEDIUM',
        'EXTENDED',
    ];

    protected function configure()
    {
        $help = <<<'HELP'
<comment>TYPE OPTIONS</comment>

<info>QUICK</info>
            Do not scan the rows to check for incorrect links.
            Applies to InnoDB and MyISAM tables and views.
<info>FAST</info>
            Check only tables that have not been closed properly.
            Applies only to MyISAM tables and views; ignored for InnoDB.
<info>CHANGED</info>
            Check only tables that have been changed since the last check or that
            have not been closed properly. Applies only to MyISAM tables and views;
            ignored for InnoDB.
<info>MEDIUM</info>
            Scan rows to verify that deleted links are valid.
            This also calculates a key checksum for the rows and verifies this with a
            calculated checksum for the keys. Applies only to MyISAM tables and views;
            ignored for InnoDB.
<info>EXTENDED</info>
            Do a full key lookup for all keys for each row. This ensures that the table
            is 100% consistent, but takes a long time.
            Applies only to MyISAM tables and views; ignored for InnoDB.

<comment>InnoDB</comment>
            InnoDB tables will be optimized with the ALTER TABLE ... ENGINE=InnoDB statement.
            The options above do not apply to them.
HELP;

        $this
            ->setName('db:maintain:check-tables')
            ->setDescription('Check database tables')
            ->addOption(
                'type',
                null,
                InputOption::VALUE_OPTIONAL,
                'Check type (one of QUICK, FAST, MEDIUM, EXTENDED, CHANGED)',
                'MEDIUM'
            )
            ->addOption('repair', null, InputOption::VALUE_NONE, 'Repair tables (only MyISAM)')
            ->addOption(
                'table',
                null,
                InputOption::VALUE_OPTIONAL,
                'Process only given table (wildcards are supported)'
            )
            ->addOption(
                'format',
                null,
                InputOption::VALUE_OPTIONAL,
                'Output Format. One of [' . implode(',', RendererFactory::getFormats()) . ']'
            )
            ->setHelp($help);
    }

    /**
     * @throws \InvalidArgumentException
     *
     */
    protected function isTypeAllowed()
    {
        $type = $this->input->getOption('type');
        $type = strtoupper($type);
        if ($type && !in_array($type, $this->allowedTypes)) {
            throw new \InvalidArgumentException('Invalid type was given');
        }
    }

    /**
     * @param ProgressBar $progress
     */
    protected function progressAdvance(ProgressBar $progress)
    {
        if ($this->showProgress) {
            $progress->advance();
        }
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @throws \InvalidArgumentException
     * @throws \Exception
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
        $this->isTypeAllowed();
        $this->detectMagento($output);
        $this->dbHelper = $this->getHelper('database');
        $this->showProgress = $input->getOption('format') == null;

        if ($input->getOption('table')) {
            $resolvedTables = [
                $this->dbHelper->resolveTables(
                    ['@check'],
                    [
                        'check' => [
                            'tables' => [$input->getOption('table')],
                        ],
                    ]
                ),
            ];
            $tables = $resolvedTables[0];
        } else {
            $tables = $this->dbHelper->getTables();
        }

        $allTableStatus = $this->dbHelper->getTablesStatus();

        $tableOutput = [];

        $progress = new ProgressBar($output, 50);

        if ($this->showProgress) {
            $progress->start(count($tables));
        }

        $methods = [
            'InnoDB' => 1,
            'MEMORY' => 1,
            'MyISAM' => 1,
        ];

        foreach ($tables as $tableName) {
            if (isset($allTableStatus[$tableName]) && isset($methods[$allTableStatus[$tableName]['Engine']])) {
                $m = '_check' . $allTableStatus[$tableName]['Engine'];
                $tableOutput = array_merge($tableOutput, $this->$m($tableName));
            } else {
                $tableOutput[] = [
                    'table'     => $tableName,
                    'operation' => 'not supported',
                    'type'      => '',
                    'status'    => '',
                ];
            }
            $this->progressAdvance($progress);
        }

        if ($this->showProgress) {
            $progress->finish();
        }

        $this->getHelper('table')
            ->setHeaders(['Table', 'Operation', 'Type', 'Status'])
            ->renderByFormat($this->output, $tableOutput, $this->input->getOption('format'));

        return Command::SUCCESS;
    }

    /**
     * @param string $tableName
     * @param string $engine
     *
     * @return array
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function _queryAlterTable($tableName, $engine)
    {
        /** @var \PDO $connection */
        $connection = $this->dbHelper->getConnection($this->output);
        $start = microtime(true);
        $affectedRows = $connection->exec(sprintf('ALTER TABLE %s ENGINE=%s', $tableName, $engine));

        return [[
            'table'     => $tableName,
            'operation' => 'ENGINE ' . $engine,
            'type'      => sprintf('%15s rows', (string) $affectedRows),
            'status'    => sprintf('%.3f secs', microtime(true) - $start),
        ],
        ];
    }

    /**
     * @param string $tableName
     *
     * @return array
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function _checkInnoDB($tableName)
    {
        return $this->_queryAlterTable($tableName, 'InnoDB');
    }

    /**
     * @param string $tableName
     *
     * @return array
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function _checkMEMORY($tableName)
    {
        return $this->_queryAlterTable($tableName, 'MEMORY');
    }

    /**
     * @param string $tableName
     *
     * @return array
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function _checkMyISAM($tableName)
    {
        $table = [];
        $type = $this->input->getOption('type');
        $result = $this->_query(sprintf('CHECK TABLE %s %s', $tableName, $type));
        if ($result['Msg_text'] == self::MESSAGE_CHECK_NOT_SUPPORTED) {
            return [];
        }

        $table[] = [
            'table'     => $tableName,
            'operation' => $result['Op'],
            'type'      => $type,
            'status'    => $result['Msg_text'],
        ];

        if ($result['Msg_text'] != 'OK'
            && $this->input->getOption('repair')
        ) {
            $result = $this->_query(sprintf('REPAIR TABLE %s %s', $tableName, $type));
            if ($result['Msg_text'] != self::MESSAGE_REPAIR_NOT_SUPPORTED) {
                $table[] = [
                    'table'     => $tableName,
                    'operation' => $result['Op'],
                    'type'      => $type,
                    'status'    => $result['Msg_text'],
                ];
            }
        }
        return $table;
    }

    /**
     * @param string $sql
     *
     * @return array|bool
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function _query($sql)
    {
        /** @var \PDO $connection */
        $connection = $this->dbHelper->getConnection($this->output);
        $query = $connection->prepare($sql);
        $query->execute();

        return $query->fetch(\PDO::FETCH_ASSOC);
    }
}
