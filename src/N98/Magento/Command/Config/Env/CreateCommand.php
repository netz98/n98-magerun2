<?php

namespace N98\Magento\Command\Config\Env;

use Dflydev\DotAccessData\Data;
use N98\Magento\Command\AbstractMagentoCommand;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

/**
 * Class EnvCreateCommand
 *
 * This class is a port of EnvCreateCommand by Peter Jaap.
 * Thanks for allowing us to use the code in n98-magerun2.
 *
 * @see https://github.com/elgentos/magerun2-addons/blob/master/src/Elgentos/EnvCreateCommand.php
 * @package N98\Magento\Command\Config\Env
 */
class CreateCommand extends AbstractMagentoCommand
{
    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var OutputInterface
     */
    protected $output;

    protected function configure()
    {
        $this
            ->setName('config:env:create')
            ->setDescription('Create env file interactively')
            ->setHelp('Creates or modifies existing env.php file.');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $questionHelper = $this->getHelperSet()->get('question');

        $this->detectMagento($output);

        $updateEnvQuestion = new ConfirmationQuestion(
            '<question>env file found. Do you want to update it?</question> <comment>[Y/n]</comment> ',
            true
        );

        $envFilePath = $this->getApplication()->getMagentoRootFolder() . '/app/etc/env.php';

        if (file_exists($envFilePath) && $questionHelper->ask($input, $output, $updateEnvQuestion)) {
            $envConfig = include $envFilePath;
        } else {
            $envConfig = include __DIR__ . '/stubs/env.php';
        }

        $env = new Data($envConfig);

        $iterator = new RecursiveIteratorIterator(
            new RecursiveArrayIterator($env->export()),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $default) {
            if (!$iterator->hasChildren()) {
                for ($p = [], $i = 0, $z = $iterator->getDepth(); $i <= $z; $i++) {
                    $p[] = $iterator->getSubIterator($i)->key();
                }

                $path = implode('.', $p);
                $default = $this->getDefaultValue($path, $default);
                $question = new Question(
                    '<question>' . $path . '</question> <comment>[' . $default . ']</comment> ',
                    $default
                );

                $newValue = $questionHelper->ask($input, $output, $question);
                $env->set($path, $newValue);
            }
        }

        if (!file_exists('app/etc')) {
            if (!mkdir('app/etc', 0777, true) && !is_dir('app/etc')) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', 'app/etc'));
            }
        }

        file_put_contents($envFilePath, "<?php\n\nreturn " . EnvHelper::exportVariable($env->export()) . ";\n");

        return Command::SUCCESS;
    }

    /**
     * @param string $path
     * @param $default
     * @return false|string
     */
    private function getDefaultValue(string $path, $default)
    {
        if ($path === 'install.date' && empty($default)) {
            return date('D, d M Y H:i:s T');
        }

        return $default;
    }
}
