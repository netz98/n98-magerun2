<?php

namespace N98\Magento\Command\Route;

use N98\Magento\Command\AbstractMagentoCommand;
use N98\Util\Console\Helper\Table\Renderer\RendererFactory;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ListCommand extends AbstractMagentoCommand
{
    /**
     * @var \Magento\Framework\Module\Dir\Reader
     */
    private $reader;

    protected function configure()
    {
        $this
            ->setName('route:list')
            ->setDescription('Lists all registered routes')
            ->addOption(
                'module',
                null,
                InputOption::VALUE_OPTIONAL,
                'Specific module'
            )->addOption(
                'area',
                null,
                InputOption::VALUE_OPTIONAL,
                'Area code'
            )->addOption(
                'format',
                null,
                InputOption::VALUE_OPTIONAL,
                'Output Format. One of [' . implode(',', RendererFactory::getFormats()) . ']'
            );
    }

    public function inject(\Magento\Framework\Module\Dir\Reader $reader)
    {
        $this->reader = $reader;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $routes = $this->reader->getActionFiles();

        $table = [];
        foreach ($routes as $uri => $actionPath) {
            $table[] = [
                $uri,
                $actionPath
            ];
        }

        $this->getHelper('table')
            ->setHeaders(['URI', 'Module'])
            ->renderByFormat($output, $table, $input->getOption('format'))
        ;
    }
}
