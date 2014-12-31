<?php
namespace N98\Magento\Command\Cache;

use Magento\Framework\App\Cache\Type\ConfigSegment;
use N98\Magento\Command\AbstractMagentoCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use N98\Util\Console\Helper\Table\Renderer\RendererFactory;

class ListCommand extends AbstractMagentoCommand
{
    protected $cacheTypes = [];

    public function getTypes()
    {
        return $this->cacheTypes;
    }

    protected function configure()
    {
        $this
            ->setName('cache:list')
            ->setDescription('Lists all magento caches')
            ->addOption(
                'enabled',
                null,
                InputOption::VALUE_OPTIONAL,
                'Filter the list to display only enabled [1] or disabled [0] cache types'
            )
            ->addOption(
                'format',
                null,
                InputOption::VALUE_OPTIONAL,
                'Output Format. One of [' . implode(',', RendererFactory::getFormats()) . ']'
            )
        ;
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectMagento($output, true);

        if ($input->getOption('format') == null) {
            $this->writeSection($output, 'Magento Cache Types');
        }

        $this->initMagento();

        $this->cacheTypes = $this->getObjectManager()
                                 ->get('\Magento\Framework\App\DeploymentConfig')
                                 ->getSegment(ConfigSegment::SEGMENT_KEY);

        $tableData = [];

        foreach ($this->cacheTypes as $name => $isEnabled) {
            // If 'enabled' option is set, filter those who match
            if (! is_null($input->getOption('enabled')) && $input->getOption('enabled') != $isEnabled) {
                unset($this->cacheTypes[$name]);
                continue;
            }

            $tableData[] = [$name, $isEnabled];
        }

        $this->getHelper('table')
             ->setHeaders(array('Name', 'Enabled'))
             ->renderByFormat($output, $tableData, $input->getOption('format'));
    }
}