<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Developer\Di\Preference;

use Exception;
use InvalidArgumentException;
use Magento\Framework\ObjectManager\ConfigLoaderInterface;
use N98\Magento\Command\AbstractMagentoCommand;
use N98\Util\Console\Helper\Table\Renderer\RendererFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

/**
 * Class ListCommand
 * @package N98\Magento\Command\Developer\Module\Preference
 */
class ListCommand extends AbstractMagentoCommand
{
    protected $areas = [
        'global',
        'adminhtml',
        'frontend',
        'crontab',
        'webapi_rest',
        'webapi_soap',
        'graphql',
        'doc',

        // 'admin' has been declared deprecated since 5448233
        // https://github.com/magento/magento2/commit/5448233#diff-5bc6336cfbfd5aeb18404416f508b6c4
        'admin',
    ];

    protected function configure()
    {
        $this
            ->setName('dev:di:preferences:list')
            ->setDescription('Lists all registered preferences')
            ->addArgument(
                'area',
                InputArgument::OPTIONAL,
                'Filter observers in specific area. One of [' . implode(',', $this->areas) . ']'
            )
            ->addOption(
                'format',
                null,
                InputOption::VALUE_OPTIONAL,
                'Output Format. One of [' . implode(',', RendererFactory::getFormats()) . ']'
            );
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $area = $input->getArgument('area');

        if ($area === null || !in_array($area, $this->areas)) {
            $choices = [];
            foreach ($this->areas as $key => $area) {
                $choices[$key + 1] = '<comment>[' . $area . ']</comment> ';
            }

            $question = new ChoiceQuestion('<question>Please select an area:</question>', $choices);
            $question->setValidator(function ($areaIndex) {
                if (!in_array($areaIndex - 1, range(0, count($this->areas) - 1), true)) {
                    throw new InvalidArgumentException('Invalid selection.' . $areaIndex);
                }

                return $this->areas[$areaIndex - 1];
            });
            $area = $this->getHelper('question')->ask($input, $output, $question);
        }
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectMagento($output);
        if (!$this->initMagento()) {
            return Command::FAILURE;
        }

        $configLoader = $this->getObjectManager()->get(ConfigLoaderInterface::class);
        $data = $configLoader->load($input->getArgument('area'));

        $table = [];

        foreach ($data['preferences'] as $for => $type) {
            $table[] = [
                $for,
                $type,
            ];
        }

        if ($input->getOption('format') == null) {
            $this->writeSection($output, 'Magento Modules');
        }

        $this->getHelper('table')
            ->setHeaders(['for', 'type'])
            ->renderByFormat($output, $table, $input->getOption('format'));

        return Command::SUCCESS;
    }
}
