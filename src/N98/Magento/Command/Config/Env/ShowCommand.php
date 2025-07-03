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
use N98\Util\Console\Helper\Table\Renderer\RendererFactory;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;

/**
 * Class ShowCommand
 * @package N98\Magento\Command\Config\Env
 */
class ShowCommand extends AbstractMagentoCommand
{
    protected function configure()
    {
        $this
            ->setName('config:env:show')
            ->setDescription('List env.php file')
            ->addArgument('key', InputArgument::OPTIONAL, 'Key to show.')
            ->addOption(
                'format',
                null,
                InputOption::VALUE_OPTIONAL,
                'Output Format. One of [' . implode(',', RendererFactory::getFormats()) . ']'
            );
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

        $keyToShow = $input->getArgument('key');

        $envConfig = include $envFilePath;
        $env = new Data($envConfig);

        $cloner = new VarCloner();
        $cloner->setMaxItems(-1);
        $cloner->setMaxString(-1);
        $dumper = new CliDumper();
        $dumper->setColors(true);

        $flattenArray = $this->flatten($env->export());

        ksort($flattenArray);

        if ($keyToShow !== null) {
            if (!isset($flattenArray[$keyToShow])) {
                throw new InvalidArgumentException('Unknown key: ' . $keyToShow);
            }

            $output->writeln($flattenArray[$keyToShow]);
        } else {
            $table = [];

            foreach ($flattenArray as $configKey => $configValue) {
                // prevents a crash when a key contains an empty array as value
                if ($configValue === []) {
                    $configValue = '';
                }

                $table[] = [
                    'key' => $configKey,
                    'value' => $configValue,
                ];
            }

            $this->getHelper('table')
                ->setHeaders(['key', 'value'])
                ->renderByFormat($output, $table, $input->getOption('format'));
        }

        return Command::SUCCESS;
    }

    /**
     * @param array $data
     * @param string $pathPrefix
     * @return array
     * @link https://github.com/dflydev/dflydev-dot-access-data/issues/16#issuecomment-699638023
     */
    private function flatten(array $data, string $pathPrefix = ''): array
    {
        $ret = [];

        foreach ($data as $key => $value) {
            $fullKey = ltrim($pathPrefix . '.' . $key, '.');
            if (is_array($value)) {
                $ret += $this->flatten($value, $fullKey);
            } else {
                $ret[$fullKey] = $value;
            }
        }

        return $ret;
    }
}
