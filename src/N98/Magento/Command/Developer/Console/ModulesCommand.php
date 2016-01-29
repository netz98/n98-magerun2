<?php

namespace N98\Magento\Command\Developer\Console;

use N98\Util\BinaryString;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\Module\ModuleListInterface;

class ModulesCommand extends AbstractConsoleCommand
{
    protected function configure()
    {
        $this
            ->setName('modules')
            ->addArgument('vendor', InputArgument::OPTIONAL, 'Vendor to filter', '')
            ->setDescription('List all modules')
        ;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $moduleList = $this->get(ModuleListInterface::class);

        $modules = array_keys($moduleList->getAll());

        $vendorArgument = $input->getArgument('vendor');
        if ($vendorArgument !== '') {
            $modules = array_filter($modules, function($module) use ($vendorArgument) {
                return BinaryString::startsWith($module, ucfirst($vendorArgument));
            });
        }

        $output->writeln($modules);
    }
}