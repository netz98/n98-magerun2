<?php

namespace N98\Magento\Command\System\Setup;

use N98\Magento\Command\AbstractMagentoCommand;
use N98\JUnitXml\Document as JUnitXmlDocument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use N98\Util\Console\Helper\Table\Renderer\RendererFactory;

class CompareVersionsCommand extends AbstractMagentoCommand
{
    /**
     * @var \Magento\Framework\Module\ResourceInterface
     */
    protected $moduleResource;

    /**
     * @var \Magento\Framework\Module\ModuleListInterface
     */
    protected $moduleList;

    /**
     * @var \Magento\Framework\Module\ResourceResolverInterface
     */
    protected $resourceResolver;

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
     * @param \Magento\Framework\Module\ModuleListInterface $moduleList
     * @param \Magento\Framework\Module\ResourceResolverInterface $resourceResolver
     * @param \Magento\Framework\Module\ResourceInterface $moduleResource
     */
    public function inject(
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Framework\Module\ResourceResolverInterface $resourceResolver,
        \Magento\Framework\Module\ResourceInterface $moduleResource
    ) {
        $this->moduleList = $moduleList;
        $this->resourceResolver = $resourceResolver;
        $this->moduleResource = $moduleResource;
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $time = microtime(true);
        $ignoreDataUpdate   = $input->getOption('ignore-data');

        $headers = array('Setup', 'Module', 'DB', 'Data', 'Status');
        if ($ignoreDataUpdate) {
            unset($headers[array_search('Data', $headers)]);
        }

        $errorCounter = 0;
        $table = array();
        foreach ($this->moduleList->getAll() as $moduleName => $moduleInfo) {
            foreach ($this->resourceResolver->getResourceList($moduleName) as $resourceName) {
                $moduleVersion  = $moduleInfo['schema_version'];
                $dbVersion      = $this->moduleResource->getDbVersion($resourceName);
                if (!$ignoreDataUpdate) {
                    $dataVersion = $this->moduleResource->getDataVersion($resourceName);
                }
                $ok = $dbVersion == $moduleVersion;
                if ($ok && !$ignoreDataUpdate) {
                    $ok = $dataVersion == $moduleVersion;
                }
                if (!$ok) {
                    $errorCounter++;
                }

                $row = array(
                    'Setup'     => $resourceName,
                    'Module'    => $moduleVersion,
                    'DB'        => $dbVersion,
                );

                if (!$ignoreDataUpdate) {
                    $row['Data-Version'] = $dataVersion;
                }
                $row['Status'] = $ok ? 'OK' : 'Error';
                $table[] = $row;
            }
        }

        //if there is no output format
        //highlight the status
        //and show error'd rows at bottom
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
