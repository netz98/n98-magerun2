<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Developer\Console;

use Magento\Framework\Module\ModuleListInterface;
use N98\Util\BinaryString;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ModulesCommand
 * @package N98\Magento\Command\Developer\Console
 */
class ModulesCommand extends AbstractConsoleCommand
{
    protected function configure()
    {
        $this
            ->setName('modules')
            ->addArgument('vendor', InputArgument::OPTIONAL, 'Vendor to filter', '')
            ->setDescription('List all modules');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $moduleList = $this->create(ModuleListInterface::class);

        $modules = array_keys($moduleList->getAll());

        $vendorArgument = $input->getArgument('vendor');
        if ($vendorArgument !== '') {
            $modules = array_filter($modules, function ($module) use ($vendorArgument) {
                return BinaryString::startsWith($module, ucfirst($vendorArgument));
            });
        }

        $output->writeln('<strong>' . implode(PHP_EOL, $modules) . '</strong>');

        return Command::SUCCESS;
    }
}
