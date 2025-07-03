<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\SearchEngine;

use N98\Magento\Command\AbstractMagentoCommand;
use N98\Util\Console\Helper\Table\Renderer\RendererFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ListCommand
 * @package N98\Magento\Command\SearchEngine
 */
class ListCommand extends AbstractMagentoCommand
{
    /**
     * @var \Magento\Search\Model\Adminhtml\System\Config\Source\Engine
     */
    private $searchEngineConfig;

    protected function configure()
    {
        $this
            ->setName('search:engine:list')
            ->setDescription('Lists all registered search engines')
            ->addOption(
                'format',
                null,
                InputOption::VALUE_OPTIONAL,
                'Output Format. One of [' . implode(',', RendererFactory::getFormats()) . ']'
            );
    }

    /**
     * @param \Magento\Search\Model\Adminhtml\System\Config\Source\Engine $searchEngineConfig
     */
    public function inject(\Magento\Search\Model\Adminhtml\System\Config\Source\Engine $searchEngineConfig)
    {
        $this->searchEngineConfig = $searchEngineConfig;
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectMagento($output, true);
        if (!$this->initMagento()) {
            return Command::FAILURE;
        }

        $searchEngines = $this->searchEngineConfig->toOptionArray();

        $table = [];
        foreach ($searchEngines as $searchEngine) {
            // remove "please select" value
            if (empty($searchEngine['value'])) {
                continue;
            }

            $table[] = [
                $searchEngine['value'],
                $searchEngine['label'],
            ];
        }

        $this->getHelper('table')
            ->setHeaders(['code', 'label'])
            ->renderByFormat($output, $table, $input->getOption('format'));

        return Command::SUCCESS;
    }
}
