<?php
namespace N98\Magento\Command\Cache;

use Magento\Framework\App\Cache\TypeList as CacheTypeList;
use Magento\Framework\App\DeploymentConfig;
use N98\Magento\Command\AbstractMagentoCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use N98\Util\Console\Helper\Table\Renderer\RendererFactory;

class ListCommand extends AbstractMagentoCommand
{
    /**
     * @var CacheTypeList
     */
    private $cacheTypeList;

    /**
     * @return array
     */
    public function getTypes()
    {
        return $this->cacheTypeList->getTypes();
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

    public function inject(
        CacheTypeList $cacheTypeList
    ) {
        $this->cacheTypeList = $cacheTypeList;
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

        $cacheTypes = $this->getTypes();

        $tableData = [];

        foreach ($cacheTypes as $cacheType) {

            // If 'enabled' option is set, filter those who match
            if (!is_null($input->getOption('enabled')) && $input->getOption('enabled') != $cacheType->getStatus()) {
                unset($cacheTypes[$cacheType->getId()]);
                continue;
            }

            $tableData[] = [$cacheType->getId(), $cacheType->getCacheType(), $cacheType->getStatus()];
        }

        $this->getHelper('table')
                ->setHeaders(array('Name', 'Type', 'Enabled'))
                ->renderByFormat($output, $tableData, $input->getOption('format'));
    }
}
