<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\System;

use LogicException;
use Magento\Framework\App\State;
use Magento\Store\Model\StoreManagerInterface;
use N98\Magento\Command\AbstractMagentoCommand;
use N98\Magento\Command\CommandAware;
use N98\Magento\Command\CommandConfigAware;
use N98\Magento\Command\System\Check\Result;
use N98\Magento\Command\System\Check\ResultCollection;
use N98\Magento\Framework\AreaAware;
use N98\Util\Console\Helper\InjectionHelper;
use N98\Util\Console\Helper\Table\Renderer\RendererFactory;
use N98\Util\Unicode\Charset;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CheckCommand
 *
 * @package N98\Magento\Command\System
 */
class CheckCommand extends AbstractMagentoCommand
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Command config
     *
     * @var array
     */
    private $config;

    /**
     * @var State
     */
    private $appState;

    /**
     * @var array
     */
    private $registry;

    protected function configure()
    {
        $this
            ->setName('sys:check')
            ->setDescription('Checks Magento System')
            ->addOption(
                'format',
                null,
                InputOption::VALUE_OPTIONAL,
                'Output Format. One of [' . implode(',', RendererFactory::getFormats()) . ']'
            );

        $help = <<<HELP
- Checks missing files and folders
- Security
- PHP Extensions (Required and Bytecode Cache)
- MySQL InnoDB Engine
HELP;
        $this->setHelp($help);
    }

    public function inject(State $appState, StoreManagerInterface $storeManager)
    {
        $this->appState = $appState;
        $this->storeManager = $storeManager;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectMagento($output);
        if (!$this->initMagento()) {
            return Command::FAILURE;
        }

        $this->config = $this->getCommandConfig();

        $results = new ResultCollection();

        foreach ($this->config['checks'] as $checkGroup => $checkGroupClasses) {
            $results->setResultGroup($checkGroup);
            foreach ($checkGroupClasses as $checkGroupClass) {
                $this->_invokeCheckClass($results, $checkGroupClass);
            }
        }

        if ($input->getOption('format')) {
            $this->_printTable($input, $output, $results);
        } else {
            $this->_printResults($output, $results);
        }

        return Command::SUCCESS;
    }

    /**
     * @param ResultCollection $results
     * @param string $checkGroupClass name
     * @throws \Exception
     */
    protected function _invokeCheckClass(ResultCollection $results, $checkGroupClass)
    {
        $check = $this->_createCheck($checkGroupClass);

        $areaCode = 'adminhtml';

        if ($check instanceof AreaAware) {
            $areaCode = $check->getAreaCode();
        }

        $this->appState->emulateAreaCode(
            $areaCode,
            function () use ($check, $results, $checkGroupClass) {
                switch (true) {
                    case $check instanceof Check\SimpleCheck:
                        $check->check($results);
                        break;

                    case $check instanceof Check\StoreCheck:
                        $this->checkStores($results, $checkGroupClass, $check);
                        break;

                    case $check instanceof Check\WebsiteCheck:
                        $this->checkWebsites($results, $checkGroupClass, $check);
                        break;

                    default:
                        throw new LogicException(
                            sprintf('Unhandled check-class "%s"', $checkGroupClass)
                        );
                }
            }
        );
    }

    /**
     * @param OutputInterface  $output
     * @param ResultCollection $results
     */
    protected function _printResults(OutputInterface $output, ResultCollection $results)
    {
        foreach ($results as $resultGroupName => $groupResults) {
            if (count($groupResults) > 0) {
                $this->writeSection($output, str_pad(strtoupper($resultGroupName), 60, ' ', STR_PAD_BOTH));
            } else {
                continue;
            }

            foreach ($groupResults as $result) {
                if ($result->getMessage()) {
                    switch ($result->getStatus()) {
                        case Result::STATUS_SKIPPED:
                            break;

                        case Result::STATUS_WARNING:
                        case Result::STATUS_ERROR:
                            $output->write('<error>' . Charset::convertInteger(Charset::UNICODE_CROSS_CHAR) . '</error> ');
                            break;

                        case Result::STATUS_OK:
                        default:
                            $output->write(
                                '<info>' . Charset::convertInteger(Charset::UNICODE_CHECKMARK_CHAR) . '</info> '
                            );
                            break;
                    }
                    $output->writeln($result->getMessage());
                }
            }
        }
    }

    /**
     * @param InputInterface   $input
     * @param OutputInterface  $output
     * @param ResultCollection $results
     */
    protected function _printTable(InputInterface $input, OutputInterface $output, ResultCollection $results)
    {
        $table = [];

        foreach ($results as $groupResults) {
            if (count($groupResults) === 0) {
                continue;
            }

            foreach ($groupResults as $result) {
                /* @var $result Result */
                $table[] = [
                    $result->getResultGroup(),
                    strip_tags($result->getMessage()),
                    $result->getStatus(),
                ];
            }
        }

        $this->getHelper('table')
            ->setHeaders(['Group', 'Message', 'Result'])
            ->renderByFormat($output, $table, $input->getOption('format'));
    }

    /**
     * @param string $checkGroupClass
     *
     * @return object
     * @throws \ReflectionException
     */
    private function _createCheck($checkGroupClass)
    {
        /* @var $injection InjectionHelper */
        $injection = $this->getHelper('injection');
        $check = $injection->constructorInjection($checkGroupClass, $this->getObjectManager());

        if ($check instanceof CommandAware) {
            $check->setCommand($this);
        }
        if ($check instanceof CommandConfigAware) {
            $check->setCommandConfig($this->config);

            return $check;
        }

        return $check;
    }

    /**
     * @param ResultCollection $results
     * @param string           $context
     * @param string           $checkGroupClass
     */
    private function _markCheckWarning(ResultCollection $results, $context, $checkGroupClass)
    {
        $result = $results->createResult();
        $result->setMessage(
            '<error>No ' . $context . ' configured to run store check:</error> <comment>' .
            basename($checkGroupClass) . '</comment>'
        );
        $result->setStatus($result::STATUS_WARNING);
        $results->addResult($result);
    }

    /**
     * @param ResultCollection $results
     * @param string           $checkGroupClass name
     * @param Check\StoreCheck $check
     */
    private function checkStores(ResultCollection $results, $checkGroupClass, Check\StoreCheck $check)
    {
        if (!$stores = $this->storeManager->getStores()) {
            $this->_markCheckWarning($results, 'stores', $checkGroupClass);
        }
        foreach ($stores as $store) {
            $check->check($results, $store);
        }
    }

    /**
     * @param ResultCollection   $results
     * @param string             $checkGroupClass name
     * @param Check\WebsiteCheck $check
     */
    private function checkWebsites(ResultCollection $results, $checkGroupClass, Check\WebsiteCheck $check)
    {
        if (!$websites = $this->storeManager->getWebsites()) {
            $this->_markCheckWarning($results, 'websites', $checkGroupClass);
        }
        foreach ($websites as $website) {
            $check->check($results, $website);
        }
    }
}
