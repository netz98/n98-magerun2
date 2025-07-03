<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Config\Env;

use Dflydev\DotAccessData\Data;
use InvalidArgumentException;
use N98\Magento\Command\AbstractMagentoCommand;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class SetCommand
 * @package N98\Magento\Command\Config\Env
 */
class SetCommand extends AbstractMagentoCommand
{
    const INPUT_FORMAT_PLAIN = 'plain';
    const INPUT_FORMAT_JSON  = 'json';

    protected function configure()
    {
        $this
            ->setName('config:env:set')
            ->setDescription('Set value in env.php')
            ->addArgument('key', InputArgument::REQUIRED, 'config key')
            ->addArgument('value', InputArgument::OPTIONAL, 'config value', '')
            ->addOption(
                'input-format',
                null,
                InputOption::VALUE_REQUIRED,
                'Input Format. One of [' . implode(',', $this->getInputFormats()) . ']',
                self::INPUT_FORMAT_PLAIN
            )
            ->setHelp('Modify config in env.php file. Use config:env:show command to see the existing values.');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectMagento($output);

        $envFilePath = $this->getApplication()->getMagentoRootFolder() . '/app/etc/env.php';

        if (!file_exists($envFilePath)) {
            throw new RuntimeException('env.php file does not exist.');
        }

        $envConfig = include $envFilePath;
        $env = new Data($envConfig);

        $key = $input->getArgument('key');
        $value = $input->getArgument('value');
        $parsedValue = $this->parseValue($value, $input);

        $checksumBefore = sha1(json_encode($env->export(), JSON_THROW_ON_ERROR));
        $env->set($key, $parsedValue);
        $checksumAfter = sha1(json_encode($env->export(), JSON_THROW_ON_ERROR));

        if ($checksumBefore !== $checksumAfter) {
            if (@file_put_contents(
                $envFilePath,
                "<?php\n\nreturn " . EnvHelper::exportVariable($env->export()) . ";\n"
            )
            ) {
                $output->writeln(sprintf('<info>Config <comment>%s</comment> successfully set to <comment>%s</comment></info>', $key, $value));
            } else {
                $output->writeln('<error>Config value could not be set</error>');

                return Command::FAILURE;
            }
        } else {
            $output->writeln('<info>Config was already set</info>');
        }

        return Command::SUCCESS;
    }

    private function parseValue(string $value, InputInterface $input)
    {
        $parsedValue = $value;

        $inputFormat = $input->getOption('input-format');
        if (!in_array($inputFormat, $this->getInputFormats(), true)) {
            throw new InvalidArgumentException('Input format ' . $inputFormat . ' is not supported, please use one of [' . implode(',', $this->getInputFormats()) . ']');
        }

        switch ($inputFormat) {
            case self::INPUT_FORMAT_JSON:
                $parsedValue = json_decode($value, true);
                if ($parsedValue === null) {
                    throw new InvalidArgumentException('Can\'t parse value as json: ' . $value);
                }
                break;
        }

        return $parsedValue;
    }

    private function getInputFormats(): array
    {
        return [
            self::INPUT_FORMAT_PLAIN,
            self::INPUT_FORMAT_JSON,
        ];
    }
}
