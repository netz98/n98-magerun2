<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Developer\Module\Preference;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Framework\Module\ModuleListInterface;
use N98\Magento\Command\AbstractMagentoCommand;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ConflictsCommand extends AbstractMagentoCommand
{
    private ModuleListInterface $moduleList;
    private ComponentRegistrarInterface $componentRegistrar;
    private ConflictDetector $detector;

    public function inject(
        ModuleListInterface $moduleList,
        ComponentRegistrarInterface $componentRegistrar,
        ConflictDetector $detector
    ): void {
        $this->moduleList = $moduleList;
        $this->componentRegistrar = $componentRegistrar;
        $this->detector = $detector;
    }

    protected function configure(): void
    {
        $this->setName('dev:module:preference:conflicts')
            ->setDescription('Find potential conflicts between module DI preferences in the same scope')
            ->setHelp(
                'Inspect enabled registered modules only, excluding primary app/etc/di.xml. '
                . 'Different types for the same target within one scope are potential conflicts, '
                . 'not proof of breakage. Global and area overrides are not compared. '
                . 'This diagnostic command does not repair or change configuration. '
                . 'Findings and clean results both exit successfully.'
            )
            ->addOption('area', null, InputOption::VALUE_REQUIRED, 'Inspect only this scope (e.g. global or frontend)')
            ->addFormatOption();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $paths = [];
            foreach ($this->moduleList->getNames() as $module) {
                $path = $this->componentRegistrar->getPath(ComponentRegistrar::MODULE, $module);
                if ($path === null) {
                    throw new RuntimeException('Enabled module has no registered path: ' . $module);
                }
                $paths[$module] = $path;
            }
            $rows = $this->detector->detect($paths, $input->getOption('area'));
        } catch (RuntimeException $exception) {
            $output->writeln('<error>' . OutputFormatter::escape($exception->getMessage()) . '</error>');

            return Command::FAILURE;
        }

        $format = $input->getOption('format');
        if ($format === null) {
            if ($rows === []) {
                $output->writeln('<info>No preference conflicts found.</info>');

                return Command::SUCCESS;
            }
            $output->writeln('<comment>Potential preference conflicts (same-scope declarations):</comment>');
        }
        $this->getHelper('table')->setHeaders(['scope', 'target', 'type', 'module', 'file', 'line'])
            ->renderByFormat($output, array_map('array_values', $rows), $format);

        return Command::SUCCESS;
    }
}
