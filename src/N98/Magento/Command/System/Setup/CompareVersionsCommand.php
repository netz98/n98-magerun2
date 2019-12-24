<?php
/*
 * this file is part of magerun
 *
 * @author Tom Klingenberg <https://github.com/ktomk>
 */

namespace N98\Magento\Command\System\Setup;

use N98\Util\ArrayFunctions;
use N98\Util\Console\Helper\Table\Renderer\RendererFactory;
use N98\Util\Console\Helper\TableHelper;
use N98\Util\JUnitSession;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CompareVersionsCommand
 * @package N98\Magento\Command\System\Setup
 */
class CompareVersionsCommand extends AbstractSetupCommand
{
    /**
     * Setup
     */
    protected function configure()
    {
        $this
            ->setName('sys:setup:compare-versions')
            ->addOption('ignore-data', null, InputOption::VALUE_NONE, 'Ignore data updates')
            ->addOption('log-junit', null, InputOption::VALUE_REQUIRED, 'Log output to a JUnit xml file.')
            ->addOption(
                'format',
                null,
                InputOption::VALUE_OPTIONAL,
                'Output Format. One of [' . implode(',', RendererFactory::getFormats()) . ']'
            )
            ->setDescription('Compare module version with setup_module table.');
        $help = <<<HELP
Compares module version with saved setup version in `setup_module` table and displays version mismatch.
HELP;
        $this->setHelp($help);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectMagento($output, true);

        if (!$this->initMagento()) {
            return 0;
        }

        $junit = $input->getOption('log-junit') ? new JUnitSession($input->getOption('log-junit')) : null;

        $ignoreDataUpdate = $input->getOption('ignore-data');
        if ($ignoreDataUpdate) {
            $headers = ['Setup', 'Module', 'DB', 'Status'];
        } else {
            $headers = ['Setup', 'Module', 'DB', 'Data', 'Status'];
        }

        $table = $this->getModuleTable($ignoreDataUpdate, $headers, $errorCount);

        $this->output($input, $output, $headers, $table, $junit, $errorCount);

        return 0;
    }

    /**
     * @param array $data
     * @param JUnitSession $session
     * @throws \Exception
     */
    protected function logJUnit(array $data, JUnitSession $session)
    {
        $suite = $session->addTestSuite();
        $suite->setName('n98-magerun2: ' . $this->getName());
        $suite->setTimestamp(new \DateTime());
        $suite->setTime($session->getDuration());

        $testCase = $suite->addTestCase();
        $testCase->setName('Magento Setup Version Test');
        $testCase->setClassname('CompareVersionsCommand');

        if (count($data) > 0) {
            foreach ($data as $moduleSetup) {
                if (false !== stripos($moduleSetup['Status'], 'error')) {
                    $testCase->addFailure(
                        'Setup Script Error',
                        'MagentoSetupScriptVersionException'
                    );
                }
            }
        }

        $session->save($session->getName());
    }

    /**
     * @param bool $ignoreDataUpdate
     * @param array $headers
     * @param int $errorCount
     * @return array
     */
    private function getModuleTable($ignoreDataUpdate, array $headers, &$errorCount)
    {
        $errorCount = 0;
        $table = [];
        $magentoModuleList = $this->getMagentoModuleList();

        foreach ($magentoModuleList as $name => $module) {
            $row = $this->mapModuleToRow($name, $module, $ignoreDataUpdate, $errorCount);

            if (empty($row)) {
                continue;
            }

            if ($ignoreDataUpdate) {
                unset($row['Data']);
            }

            $table[] = ArrayFunctions::columnOrder($headers, $row);
        }

        return $table;
    }

    private function testVersionProblem(array $row, $ignoreDataUpdate)
    {
        $moduleVersion = $row['Module'];
        $dbVersion = $row['DB'];
        $dataVersion = $row['Data'];

        if ($moduleVersion === null) {
            return true;
        }

        $result = $dbVersion === $moduleVersion;
        if (!$ignoreDataUpdate && $result && $dataVersion !== $moduleVersion) {
            $result = false;
        }

        return $result;
    }

    /**
     * @param int $errorCount
     * @return string
     */
    private function buildSetupResultMessage($errorCount)
    {
        if (0 === $errorCount) {
            return 'No setup errors were found.';
        }

        $message = sprintf(
            '%s setup error%s %s found!',
            $errorCount,
            $errorCount === 1 ? '' : 's',
            $errorCount === 1 ? 'was' : 'were'
        );

        return $message;
    }

    /**
     * format highlight the status (green/red) and show error'd rows at bottom
     *
     * @param $table
     */
    private function sortAndDecorate(&$table)
    {
        usort($table, function ($a, $b) {
            if ($a['Status'] === $b['Status']) {
                return strcmp($a['Setup'], $b['Setup']);
            }

            return $a['Status'] !== 'OK';
        });

        array_walk($table, function (&$row) {
            $status = $row['Status'];
            $availableStatus = ['OK' => 'info', 'Error' => 'error'];
            $statusString = sprintf(
                '<%s>%s</%s>',
                $availableStatus[$status],
                $status,
                $availableStatus[$status]
            );
            $row['Status'] = $statusString;
        });
    }

    /**
     * @param $ignoreDataUpdate
     * @param $errorCount
     * @param $name
     * @param $module
     * @return array
     */
    private function mapModuleToRow($name, $module, $ignoreDataUpdate, &$errorCount)
    {
        $resource = $this->getMagentoModuleResource();

        $row = [
            'Setup'  => $name,
            'Module' => $module['setup_version'],
            'DB'     => $resource->getDbVersion($name),
            'Data'   => $resource->getDataVersion($name),
        ];

        if (empty($row['Module'])
            && empty($row['DB'])
            && empty($row['Data'])
        ) {
            return [];
        }

        $test = $this->testVersionProblem($row, $ignoreDataUpdate);

        if (!$test) {
            $errorCount++;
        }

        $row['Status'] = $test ? 'OK' : 'Error';

        return $row;
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param array $headers
     * @param array $table
     * @param JUnitSession $junit
     * @param int $errorCount
     * @throws \Exception
     */
    private function output(
        InputInterface $input,
        OutputInterface $output,
        $headers,
        $table,
        $junit,
        $errorCount
    ) {
        if ($junit) {
            $this->logJUnit($table, $junit);
        } else {
            // sort errors to bottom and decorate status with colors if no output format is specified
            if (!$input->getOption('format')) {
                $this->sortAndDecorate($table);
            }

            /** @var $table TableHelper */
            $tableHelper = $this->getHelper('table');
            $tableHelper
                ->setHeaders($headers)
                ->renderByFormat($output, $table, $input->getOption('format'));

            // output summary line if no output format is specified
            if (!$input->getOption('format')) {
                $this->writeSection(
                    $output,
                    $this->buildSetupResultMessage($errorCount),
                    $errorCount > 0 ? 'error' : 'info'
                );
            }
        }
    }
}
