<?php

namespace N98\Magento\Command\System\Setup;

use N98\JUnitXml\Document as JUnitXmlDocument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use N98\Util\Console\Helper\Table\Renderer\RendererFactory;

class CompareVersionsCommand extends AbstractSetupCommand
{
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
            ->setDescription('Compare module version with core_resource table.');
        $help = <<<HELP
Compares module version with saved setup version in `core_resource` table and displays version mismatch.
HELP;
        $this->setHelp($help);
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $time = microtime(true);
        $ignoreDataUpdate = $input->getOption('ignore-data');

        $headers = array('Setup', 'Module', 'DB', 'Data', 'Status');
        if ($ignoreDataUpdate) {
            unset($headers[array_search('Data', $headers)]);
        }

        $errorCounter = 0;
        $table = array();
        foreach ($this->getModuleList()->getAll() as $moduleName => $moduleInfo) {

            $moduleVersion = $moduleInfo['setup_version'];
            $dbVersion     = $this->getResource()->getDbVersion($moduleName);
            if (!$ignoreDataUpdate) {
                $dataVersion = $this->getResource()->getDataVersion($moduleName);
            }

            $ok = $dbVersion == $moduleVersion;
            if ($ok && !$ignoreDataUpdate) {
                $ok = $dataVersion == $moduleVersion;
            }
            if (!$ok) {
                $errorCounter++;
            }

            $row = array(
                'Module' => $moduleName,
                'DB'     => $dbVersion,
                'Data'   => $dataVersion,
            );

            if (!$ignoreDataUpdate) {
                $row['Data-Version'] = $dataVersion;
            }
            $row['Status'] = $ok ? 'OK' : 'Error';
            $table[] = $row;
        }

        // If there is no output format highlight the status and show error'd rows at bottom
        if (!$input->getOption('format')) {

            usort($table, function($a, $b) {
                return $a['Status'] !== 'OK';
            });

            array_walk($table, function (&$row) {
                $status             = $row['Status'];
                $availableStatus    = array('OK' => 'info', 'Error' => 'error');
                $statusString       = sprintf(
                    '<%s>%s</%s>',
                    $availableStatus[$status],
                    $status,
                    $availableStatus[$status]
                );
                $row['Status'] = $statusString;
            });
        }

        if ($input->getOption('log-junit')) {
            $this->logJUnit($table, $input->getOption('log-junit'), microtime($time) - $time);
        } else {
            $this->getHelper('table')
                ->setHeaders($headers)
                ->renderByFormat($output, $table, $input->getOption('format'));

            //if no output format specified - output summary line
            if (!$input->getOption('format')) {
                if ($errorCounter > 0) {
                    $this->writeSection(
                        $output,
                        sprintf(
                            '%s error%s %s found!',
                            $errorCounter,
                            $errorCounter === 1 ? '' : 's',
                            $errorCounter === 1 ? 'was' : 'were'
                        ),
                        'error'
                    );
                } else {
                    $this->writeSection($output, 'No setup problems were found.', 'info');
                }
            }
        }
    }

    /**
     * @param array $data
     * @param string $filename
     * @param float $duration
     */
    protected function logJUnit(array $data, $filename, $duration)
    {
        $document = new JUnitXmlDocument();
        $suite = $document->addTestSuite();
        $suite->setName('n98-magerun2: ' . $this->getName());
        $suite->setTimestamp(new \DateTime());
        $suite->setTime($duration);

        $testCase = $suite->addTestCase();
        $testCase->setName('Magento Setup Version Test');
        $testCase->setClassname('CompareVersionsCommand');
        if (count($data) > 0) {
            foreach ($data as $moduleSetup) {
                if (stristr($moduleSetup['Status'], 'error')) {
                    $testCase->addFailure(
                        sprintf(
                            'Setup Script Error: [Setup %s]',
                            $moduleSetup['Setup']
                        ),
                        'MagentoSetupScriptVersionException'
                    );
                }
            }
        }

        $document->save($filename);
    }
}
