<?php
/**
 * @copyright Copyright (c) netz98 GmbH (https://www.netz98.de)
 *
 * @see PROJECT_LICENSE.txt
 */

declare(strict_types=1);

namespace N98\Magento\Command\Magerun;

use N98\Magento\Command\AbstractMagentoCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class ConfigDumpCommand extends AbstractMagentoCommand
{
    protected function configure()
    {
        $this
            ->setName('magerun:config:dump')
            ->addOption('only-dist', null, InputOption::VALUE_NONE, 'Only dump the dist config')
            ->addOption('raw', 'r', InputOption::VALUE_NONE, 'Dump the raw config without formatting')
            ->addOption('indent', 'i', InputOption::VALUE_OPTIONAL, 'Indentation level', 4)
            ->setDescription('Dumps the merged YAML config of the magerun config files.');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $configLoader = $this->getApplication()->getConfigurationLoader();

        if ($input->getOption('only-dist')) {
            $data = $configLoader->getDistConfig();
        } else {
            $data = $this->getApplication()->getConfig();
        }

        if ($input->getOption('raw')) {
            $output->writeln(Yaml::dump($data, 10, (int) $input->getOption('indent')));

            return Command::SUCCESS;
        }

        // Optional: Define custom styles if needed
        $output->getFormatter()->setStyle('key', new OutputFormatterStyle('cyan', null, ['bold']));
        $output->getFormatter()->setStyle('string', new OutputFormatterStyle('green'));
        $output->getFormatter()->setStyle('number', new OutputFormatterStyle('magenta'));
        $output->getFormatter()->setStyle('bool', new OutputFormatterStyle('yellow'));

        $yaml = Yaml::dump($data, 10, (int) $input->getOption('indent'));

        // Highlight keys and values using Symfony formatter tags
        $highlighted = preg_replace_callback('/^(\s*)([^:\n]+):(.*)$/m', function ($matches) {
            $indent = $matches[1];
            $key = "<key>{$matches[2]}</key>";
            $value = trim($matches[3]);

            if ($value === '') {
                return "{$indent}{$key}:";
            }

            // Value formatting
            if (in_array($value, ['true', 'false', 'null'])) {
                $value = "<bool>{$value}</bool>";
            } elseif (is_numeric($value)) {
                $value = "<number>{$value}</number>";
            } elseif (preg_match('/^["\'].*["\']$/', $value) || str_starts_with($value, '-')) {
                $value = "<string>{$value}</string>";
            } else {
                $value = "<string>{$value}</string>";
            }

            return "{$indent}{$key}: {$value}";
        }, $yaml);

        $output->writeln($highlighted);

        return Command::SUCCESS;
    }

}
