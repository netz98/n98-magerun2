<?php

namespace N98\Magento\Command\Config;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DeleteCommand extends AbstractConfigCommand
{
    /**
     * @var array
     */
    protected $_scopes = array(
        'default',
        'websites',
        'stores',
    );

    protected function configure()
    {
        $this
            ->setName('config:delete')
            ->setDescription('Deletes a store config item')
            ->addArgument('path', InputArgument::REQUIRED, 'The config path')
            ->addOption(
                'scope',
                null,
                InputOption::VALUE_OPTIONAL,
                'The config value\'s scope (default, websites, stores)',
                'default'
            )
            ->addOption('scope-id', null, InputOption::VALUE_OPTIONAL, 'The config value\'s scope ID', '0')
            ->addOption('all', null, InputOption::VALUE_NONE, 'Delete all entries by path')
        ;

        $help = <<<HELP
To delete all entries if a path you can set the option --all.
HELP;
        $this->setHelp($help);
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectMagento($output, true);
        if (!$this->initMagento()) {
            return;
        }

        $this->_validateScopeParam($input->getOption('scope'));
        $scopeId = $this->_convertScopeIdParam($input->getOption('scope'), $input->getOption('scope-id'));

        $deleted = array();

        $path = $input->getArgument('path');
        $pathArray = array();
        if (strstr($path, '*')) {
            $collection = $this->getObjectManager()->get('\Magento\Config\Model\Resource\Config\Data\Collection');
            /* @var $collection \Magento\Config\Model\Resource\Config\Data\Collection */

            $searchPath = str_replace('*', '%', $path);
            $collection->addFieldToFilter('path', array('like' => $searchPath));

            if ($scopeId = $input->getOption('scope')) {
                $collection->addFieldToFilter(
                    'scope',
                    array(
                            'eq' => $scopeId
                    )
                );
            }
            $collection->addOrder('path', 'ASC');

            foreach ($collection as $item) {
                $pathArray[] = $item->getPath();
            }
        } else {
            $pathArray[] = $path;
        }

        $configWriter = $this->getConfigWriter();
        foreach ($pathArray as $pathToDelete) {
            $deleted = array_merge($deleted, $this->_deletePath($input, $configWriter, $pathToDelete, $scopeId));
        }

        if (count($deleted) > 0) {
            $this->getHelper('table')
                ->setHeaders(array('deleted path', 'scope', 'id'))
                ->setRows($deleted)
                ->render($output);
        }
    }

    /**
     * @param InputInterface $input
     * @param \Magento\Framework\App\Config\Storage\WriterInterface $configWriter
     * @param                $path
     * @param                $scopeId
     *
     * @return array
     */
    protected function _deletePath(
        InputInterface $input,
        \Magento\Framework\App\Config\Storage\WriterInterface $configWriter,
        $path,
        $scopeId)
    {
        $deleted = array();
        if ($input->getOption('all')) {
            $storeManager = $this->getObjectManager()->get('Magento\Store\Model\StoreManager');

            // Default
            $configWriter->delete(
                $path,
                'default',
                0
            );

            $deleted[] = array(
                'path'    => $path,
                'scope'   => 'default',
                'scopeId' => 0,
            );

            foreach ($storeManager->getWebsites() as $website) {
                $configWriter->delete(
                    $path,
                    'websites',
                    $website->getId()
                );
                $deleted[] = array(
                    'path'    => $path,
                    'scope'   => 'websites',
                    'scopeId' => $website->getId(),
                );
            }

            // Delete stores
            foreach ($storeManager->getStores() as $store) {
                $configWriter->delete(
                    $path,
                    'stores',
                    $store->getId()
                );
                $deleted[] = array(
                    'path'    => $path,
                    'scope'   => 'stores',
                    'scopeId' => $store->getId(),
                );
            }
        } else {
            $configWriter->delete(
                $path,
                $input->getOption('scope'),
                $scopeId
            );

            $deleted[] = array(
                'path'    => $path,
                'scope'   => $input->getOption('scope'),
                'scopeId' => $scopeId,
            );
        }

        return $deleted;
    }
}
