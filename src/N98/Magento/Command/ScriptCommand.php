<?php

namespace N98\Magento\Command;

use N98\Util\BinaryString;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

/**
 * Class ScriptCommand
 * @package N98\Magento\Command
 */
class ScriptCommand extends AbstractMagentoCommand
{
    /**
     * @var array
     */
    protected $scriptVars = [];

    /**
     * @var string
     */
    protected $_scriptFilename = '';

    /**
     * @var bool
     */
    protected $_stopOnError = false;

    /**
     * @var null|\Magento\Framework\App\ProductMetadata
     */
    protected $productMetadata = null;

    protected function configure()
    {
        $this
            ->setName('script')
            ->addArgument('filename', InputArgument::OPTIONAL, 'Script file')
            ->addOption('define', 'd', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Defines a variable')
            ->addOption('stop-on-error', null, InputOption::VALUE_NONE, 'Stops execution of script on error')
            ->setDescription('Runs multiple n98-magerun commands');

        $help = <<<HELP
Example:

   # Set multiple config
   config:store:set "web/cookie/cookie_domain" example.com

   # Set with multiline values with "\n"
   config:store:set "general/store_information/address" "First line\nSecond line\nThird line"

   # This is a comment
   cache:flush


Optionally you can work with unix pipes.

   \$ echo "cache:flush" | n98-magerun-dev script

   \$ n98-magerun2.phar script < filename

It is even possible to create executable scripts:

Create file `test.magerun` and make it executable (`chmod +x test.magerun`):

   #!/usr/bin/env n98-magerun2.phar script

   config:store:set "web/cookie/cookie_domain" example.com
   cache:flush

   # Run a shell script with "!" as first char
   ! ls -l

   # Register your own variable (only key = value currently supported)
   \${my.var}=bar

   # Let magerun ask for variable value - add a question mark
   \${my.var}=?

   ! echo \${my.var}

   # Use resolved variables from n98-magerun in shell commands
   ! ls -l \${magento.root}/code/local

Pre-defined variables:

* \${magento.root}    -> Magento Root-Folder
* \${magento.version} -> Magento Version i.e. 1.7.0.2
* \${magento.edition} -> Magento Edition -> Community or Enterprise
* \${magerun.version} -> Magerun version i.e. 1.66.0
* \${php.version}     -> PHP Version
* \${script.file}     -> Current script file path
* \${script.dir}      -> Current script file dir

Variables can be passed to a script with "--define (-d)" option.

Example:

   $ n98-magerun2.phar script -d foo=bar filename

   # This will register the variable \${foo} with value bar.

It's possible to define multiple values by passing more than one option.
HELP;
        $this->setHelp($help);
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return function_exists('exec');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->_scriptFilename = $input->getArgument('filename');
        $this->_stopOnError = $input->getOption('stop-on-error');
        $this->_initDefines($input);
        $script = $this->_getContent($this->_scriptFilename);
        $commands = explode("\n", $script);
        $this->initScriptVars();

        foreach ($commands as $commandString) {
            $commandString = trim($commandString);
            if (empty($commandString)) {
                continue;
            }
            $firstChar = substr($commandString, 0, 1);

            switch ($firstChar) {
                case '#':
                    // comment
                    break;
                case '$':
                    // set var
                    $this->registerVariable($input, $output, $commandString);
                    break;
                case '!':
                    // run shell script
                    $this->runShellCommand($output, $commandString);
                    break;

                default:
                    $this->runMagerunCommand($input, $output, $commandString);
            }
        }

        return Command::SUCCESS;
    }

    /**
     * @param InputInterface $input
     * @throws \InvalidArgumentException
     */
    protected function _initDefines(InputInterface $input)
    {
        $defines = (array) $input->getOption('define');

        foreach ($defines as $define) {
            if (!strstr($define, '=')) {
                throw new \InvalidArgumentException('Invalid define');
            }
            $parts = BinaryString::trimExplodeEmpty('=', $define);
            list($variable, $value) = $parts + [1 => null];
            $this->scriptVars['${' . $variable . '}'] = $value;
        }
    }

    /**
     * @param string $filename
     * @throws RuntimeException
     * @internal param string $input
     * @return string
     */
    protected function _getContent($filename)
    {
        if ($filename === '-' || empty($filename)) {
            $filename = 'php://stdin';
        }
        $script = @\file_get_contents($filename);

        if (!$script) {
            throw new RuntimeException('Script file was not found');
        }

        return $script;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param $commandString
     * @return mixed
     */
    protected function registerVariable(InputInterface $input, OutputInterface $output, $commandString)
    {
        if (preg_match('/^(\$\{[a-zA-Z0-9-_.]+\})=(.+)/', $commandString, $matches)) {
            if (isset($matches[2]) && $matches[2][0] === '?') {
                // Variable is already defined
                if (isset($this->scriptVars[$matches[1]])) {
                    return $this->scriptVars[$matches[1]];
                }

                /* @var $questionHelper QuestionHelper */
                $questionHelper = $this->getHelper('question');

                /**
                 * Check for select "?["
                 */
                if (isset($matches[2][1]) && $matches[2][1] == '[') {
                    if (preg_match('/\[(.+)\]/', $matches[2], $choiceMatches)) {
                        $choices = BinaryString::trimExplodeEmpty(',', $choiceMatches[1]);
                        $question = new ChoiceQuestion(
                            sprintf(
                                '<question>Pleaase enter a value for </question> <comment>%s</comment>',
                                $matches[1]
                            ),
                            $choices
                        );
                        $this->scriptVars[$matches[1]] = $questionHelper->ask(
                            $input,
                            $output,
                            $question
                        );
                    } else {
                        throw new RuntimeException('Invalid choices');
                    }
                } else {
                    // normal input
                    $question = new Question(
                        '<question>Please enter a value for <comment>' . $matches[1] . '</comment>:</question>'
                    );
                    $question->setMaxAttempts(20);
                    $question->setValidator(function ($value) {
                        if (empty($value)) {
                            throw new \Exception('Please enter a value');
                        }

                        return $value;
                    });

                    $this->scriptVars[$matches[1]] = $questionHelper->ask(
                        $input,
                        $output,
                        $question
                    );
                }
            } else {
                $this->scriptVars[$matches[1]] = $this->_replaceScriptVars($matches[2]);
            }
        }
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param string $commandString
     * @throws RuntimeException
     * @throws \Exception
     */
    protected function runMagerunCommand(InputInterface $input, OutputInterface $output, $commandString)
    {
        $this->getApplication()->setAutoExit(false);
        $commandString = $this->_replaceScriptVars($commandString);
        $input = new StringInput($commandString);
        $exitCode = $this->getApplication()->run($input, $output);
        if ($exitCode !== 0 && $this->_stopOnError) {
            $this->getApplication()->setAutoExit(true);
            throw new RuntimeException('Script stopped with errors');
        }
    }

    /**
     * @param string $commandString
     * @return string
     * @throws \Exception
     */
    protected function _prepareShellCommand($commandString)
    {
        $commandString = ltrim($commandString, '!');

        // @TODO find a better place
        if (strstr($commandString, '${magento.root}')
            || strstr($commandString, '${magento.version}')
            || strstr($commandString, '${magento.edition}')
        ) {
            $this->initMagento();
        }
        $this->initScriptVars();
        $commandString = $this->_replaceScriptVars($commandString);

        return $commandString;
    }

    /**
     * @throws \Exception
     */
    protected function initScriptVars()
    {
        $rootFolder = $this->getApplication()->getMagentoRootFolder();
        if ($rootFolder !== null) {
            $this->scriptVars['${magento.root}'] = $rootFolder;
            $this->scriptVars['${magento.version}'] = $this->getMagentoVersion();
            $this->scriptVars['${magento.edition}'] = $this->getMagentoEdition();
        }

        $this->scriptVars['${php.version}'] = substr(phpversion(), 0, strpos(phpversion(), '-'));
        $this->scriptVars['${magerun.version}'] = $this->getApplication()->getVersion();
        $this->scriptVars['${script.file}'] = $this->_scriptFilename;
        $this->scriptVars['${script.dir}'] = dirname($this->_scriptFilename === null ? '' : $this->_scriptFilename);

        foreach (getenv() as $envName => $envValue) {
            $this->scriptVars['${env.' . $envName . '}'] = $envValue;
        }
    }

    /**
     * @param OutputInterface $output
     * @param string $commandString
     * @internal param $returnValue
     * @throws \Exception
     */
    protected function runShellCommand(OutputInterface $output, $commandString)
    {
        $commandString = $this->_prepareShellCommand($commandString);
        $returnValue = shell_exec($commandString);
        if (!empty($returnValue)) {
            $output->writeln($returnValue);
        }
    }

    /**
     * @param string $commandString
     * @return string
     */
    protected function _replaceScriptVars($commandString)
    {
        $commandString = str_replace(array_keys($this->scriptVars), $this->scriptVars, $commandString);

        return $commandString;
    }

    /**
     * @return string
     * @throws \Exception
     */
    private function getMagentoVersion()
    {
        return $this->getProductMetadata()->getVersion();
    }

    /**
     * @return string
     * @throws \Exception
     */
    private function getMagentoEdition()
    {
        return $this->getProductMetadata()->getEdition();
    }

    /**
     * @return \Magento\Framework\App\ProductMetadata
     * @throws \Exception
     */
    private function getProductMetadata()
    {
        if ($this->productMetadata === null) {
            $this->initMagento(); // obtaining the object-manager requires init Magento
            $objectManager = $this->getApplication()->getObjectManager();
            $this->productMetadata = $objectManager->get('\Magento\Framework\App\ProductMetadata');
        }

        return $this->productMetadata;
    }
}
