<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Developer\Class\Plugin;

use InvalidArgumentException;
use Magento\Framework\Interception\ObjectManager\ConfigInterface;
use Magento\Framework\ObjectManager\Config\Reader\Dom;
use N98\Magento\Command\AbstractMagentoCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListCommand extends AbstractMagentoCommand
{
    protected function configure()
    {
        $this->setName('dev:class:plugin:list')
            ->setDescription('Lists configured global plugins for a class or Class::method()')
            ->addArgument('class', InputArgument::REQUIRED, 'Class name or Class::method()');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->detectMagento($output, true);
        if (!$this->initMagento()) {
            return Command::FAILURE;
        }

        $inspector = new PluginInspector();
        try {
            [$class, $method] = $inspector->parseTarget($input->getArgument('class'));
        } catch (InvalidArgumentException $exception) {
            $output->writeln('<error>' . OutputFormatter::escape($exception->getMessage()) . '</error>');

            return Command::INVALID;
        }

        $objectManager = $this->getObjectManager();
        $reader = $objectManager->get(Dom::class);
        $config = array_replace_recursive($reader->read('primary'), $reader->read('global'));
        $omConfig = $objectManager->get(ConfigInterface::class);
        $rows = $inspector->inspect($class, $method, $config, static function (string $type) use ($omConfig): string {
            return $omConfig->getOriginalInstanceType($type);
        });
        $target = $class->getName() . ($method !== null ? '::' . $method . '()' : '');
        $output->writeln('Plugins on: ' . OutputFormatter::escape($target) . ' (global configuration)');
        if ($rows === []) {
            $output->writeln('No plugins found.');

            return Command::SUCCESS;
        }
        (new Table($output))->setHeaders(['Plugin name', 'Plugin class', 'Method observed', 'Type', 'Order', 'Status'])
            ->setRows(array_map(static function (array $row): array {
                return array_map(static function ($value): string {
                    return OutputFormatter::escape((string) $value);
                }, $row);
            }, $rows))->render();

        return Command::SUCCESS;
    }
}
