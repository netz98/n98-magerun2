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

        // @TODO YAML Pretty Printer
        $yaml = Yaml::dump($data, 2, 4);
        $output->writeln($yaml);

        return Command::SUCCESS;
    }
}
