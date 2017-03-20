<?php

namespace N98\Magento\Command\Config\Data;

use Magento\Framework\ObjectManager\ConfigLoaderInterface;
use N98\Magento\Command\AbstractMagentoCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;

class DiCommand extends AbstractMagentoCommand
{
    protected function configure()
    {
        $this
            ->setName('config:data:di')
            ->addArgument('type', InputArgument::OPTIONAL, 'Type (class)')
            ->addOption(
                'scope',
                's',
                InputOption::VALUE_OPTIONAL,
                'Config scope (global, adminhtml, frontend, webapi_rest, webapi_soap, ...)',
                'global'
            )
            ->setDescription('Dump dependency injection config')
        ;
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectMagento($output, true);
        if (!$this->initMagento()) {
            return;
        }

        /** @var ConfigLoaderInterface $configLoader */
        $configLoader = $this->getObjectManager()->get(ConfigLoaderInterface::class);
        $configDataPrimary = $configLoader->load('primary');
        $configDataScope = $configLoader->load($input->getOption('scope'));

        $configData = array_merge($configDataPrimary, $configDataScope);

        $cloner = new VarCloner();
        $cloner->setMaxItems(-1);
        $cloner->setMaxString(-1);
        $dumper = new CliDumper();

        if ($input->getArgument('type')) {
            $config = [];

            $normalizedKey = ltrim($input->getArgument('type'), '\\');
            if (isset($configData[$normalizedKey])) {
                $config[$normalizedKey] = $configData[$normalizedKey];
            }

            if (isset($configData['preferences'][$normalizedKey])) {
                $config['preferences'] = $configData['preferences'][$normalizedKey];
            }
        } else {
            $config = $configData;
        }

        $dumpContent = $dumper->dump($cloner->cloneVar($config), true);

        $output->write($dumpContent);
    }
}
