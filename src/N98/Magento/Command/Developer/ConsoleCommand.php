<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Developer;

use Exception;
use Magento\Framework\App\Area;
use Magento\Framework\App\AreaList;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\App\State as AppState;
use Magento\Framework\ObjectManager\ConfigLoaderInterface;
use N98\Magento\Command\AbstractMagentoCommand;
use N98\Magento\Command\Developer\Console\Shell;
use N98\Util\Unicode\Charset;
use PhpParser\Lexer;
use PhpParser\Parser;
use Psy\CodeCleaner;
use Psy\Configuration;
use Psy\Output\ShellOutput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ConsoleCommand
 * @package N98\Magento\Command\Developer
 */
class ConsoleCommand extends AbstractMagentoCommand
{
    /**
     * @var ProductMetadataInterface
     */
    private $productMeta;

    /**
     * @var AppState
     */
    private $appState;

    /**
     * @var ConfigLoaderInterface
     */
    private $configLoader;

    /**
     * @var AreaList
     */
    private $areaList;

    protected function configure()
    {
        $this
            ->setName('dev:console')
            ->addOption('area', 'a', InputOption::VALUE_REQUIRED, 'Area to initialize')
            ->addOption('auto-exit', 'e', InputOption::VALUE_NONE, 'Automatic exit after cmd')
            ->addOption('single-process', 's', InputOption::VALUE_NONE, 'Run without forking (single process)')
            ->addArgument('cmd', InputArgument::OPTIONAL, 'Direct code to run', '')
            ->setDescription(
                'Opens PHP interactive shell with a initialized Magento application</comment>'
            );
    }

    /**
     * @param ProductMetadataInterface $productMetadata
     * @param AppState $appState
     * @param ConfigLoaderInterface $configLoader
     * @param AreaList $areaList
     */
    public function inject(
        ProductMetadataInterface $productMetadata,
        AppState $appState,
        ConfigLoaderInterface $configLoader,
        AreaList $areaList
    ) {
        $this->productMeta = $productMetadata;
        $this->appState = $appState;
        $this->configLoader = $configLoader;
        $this->areaList = $areaList;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $initialized = false;
        try {
            $this->detectMagento($output);
            $initialized = $this->initMagento();
        } catch (Exception $e) {
            // do nothing
        }

        error_reporting(E_ERROR | E_WARNING | E_PARSE);
        $config = new Configuration();

        if ($input->getOption('single-process')) {
            $output->writeln(
                '<comment>Warning: Running console in single process mode disables process forking. Use only for DB transaction edge cases and not for daily work.</comment>'
            );
            $config->setUsePcntl(false);
        }

        $php8Parser = new Parser\Php8(new Lexer\Emulative());

        $cleaner = new CodeCleaner($php8Parser);
        $config->setCodeCleaner($cleaner);

        $consoleOutput = new ShellOutput();

        $commandConfig = $this->getCommandConfig();
        $commandsToAdd = [];
        foreach ($commandConfig['commands'] as $command) {
            $commandsToAdd[] = new $command();
        }

        $config->addCommands($commandsToAdd);
        $config->setUpdateCheck('never');

        $shell = new Shell($config);
        $shell->setScopeVariables([
            'di'              => $this->getObjectManager(),
            'dh'              => new DevelopmentHelper($this->getObjectManager()),
            'magentoVersion'  => $this->getObjectManager()->get(ProductMetadataInterface::class),
            'magerun'         => $this->getApplication(),
            'magerunInternal' => (object) ['currentModule' => ''],
        ]);

        if ($initialized) {
            $ok = Charset::convertInteger(Charset::UNICODE_CHECKMARK_CHAR);

            $areaToLoad = $input->getOption('area');

            if ($areaToLoad) {
                $this->loadArea($areaToLoad);
            }

            $edition = $this->productMeta->getEdition();
            $magentoVersion = $this->productMeta->getVersion();

            $statusMessage = sprintf(
                '<fg=black;bg=green>Magento %s %s initialized %s</fg=black;bg=green>',
                $magentoVersion,
                $edition,
                $ok
            );

            $consoleOutput->writeln($statusMessage);

            if ($areaToLoad) {
                $areaMessage = sprintf(
                    '<fg=black;bg=white>Area: %s</fg=black;bg=white>',
                    $this->appState->getAreaCode()
                );
                $consoleOutput->writeln($areaMessage);
            }
        } else {
            $consoleOutput->writeln('<fg=black;bg=yellow>Magento is not initialized.</fg=black;bg=yellow>');
        }

        $help = <<<'help'
At the prompt, type <comment>help</comment> for some help.

To exit the shell, type <comment>^D</comment>.
help;

        $consoleOutput->writeln($help);

        $cmd = $input->getArgument('cmd');

        if ($cmd === '-') {
            $cmd = 'php://stdin';
            $cmd = @\file_get_contents($cmd);
            if (OutputInterface::VERBOSITY_DEBUG <= $output->getVerbosity()) {
                $output->writeln('<info>read commands from stdin</info>');
            }
        }

        if (!empty($cmd)) {
            // Remove quotes possibly passed by command line
            $cmd = trim($cmd, '"\'');

            $cmd = $this->filterCmdCode($cmd);
            $code = preg_split('/[\n;]+/', $cmd);

            if ($input->getOption('auto-exit')) {
                $input->setInteractive(false);
            }

            $code = array_filter($code, function ($line) {
                $line = trim($line);
                return !in_array($line, ['', ';']);
            });

            $shell->addInput($code);
        }

        return $shell->run($input, $consoleOutput);
    }

    /**
     * @param string $areaToLoad
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function loadArea($areaToLoad): void
    {
        $this->appState->setAreaCode($areaToLoad);

        // load di.xml config of the defined area
        $this->getObjectManager()->configure(
            $this->configLoader->load($areaToLoad)
        );

        // load all configs of the defined are
        $this->areaList->getArea($areaToLoad)
            ->load(Area::PART_CONFIG)
            ->load(Area::PART_TRANSLATE);
    }

    /**
     * @param string $codeToFilter
     * @return string
     */
    private function filterCmdCode(string $codeToFilter): string
    {
        return str_replace(['<?php', '<?'], '', $codeToFilter);
    }
}
