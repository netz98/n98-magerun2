<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Config\Store;

use Magento\Config\Model\ResourceModel\Config\Data\Collection;
use Magento\Framework\App\Config\Storage\WriterInterface;
use N98\Magento\Command\Config\AbstractConfigCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DeleteCommand extends AbstractConfigCommand
{
    /**
     * @var Collection
     */
    private $collection;

    /**
     * @var array
     */
    protected $_scopes = [
        'default',
        'websites',
        'stores',
    ];

    protected function configure()
    {
        $this
            ->setName('config:store:delete')
            ->setDescription('Deletes a store config item')
            ->addArgument('path', InputArgument::REQUIRED, 'The config path')
            ->addOption(
                'scope',
                null,
                InputOption::VALUE_OPTIONAL,
                'The config value\'s scope (default, websites, stores)',
                'default'
            )
            ->addOption('scope-id', null, InputOption::VALUE_OPTIONAL, 'The config value\'s scope ID')
            ->addOption('all', null, InputOption::VALUE_NONE, 'Delete all entries by path');

        $help = <<<HELP
To delete all entries of a path you can set the option --all.
HELP;
        $this->setHelp($help);
    }

    /**
     * @param Collection $collection
     */
    public function inject(Collection $collection)
    {
        $this->collection = $collection;
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectMagento($output, true);
        if (!$this->initMagento()) {
            return Command::FAILURE;
        }

        $this->_validateScopeParam($input->getOption('scope'));
        $scopeId = $this->_convertScopeIdParam($input->getOption('scope'), $input->getOption('scope-id'));

        $deleted = [];

        $paths = $this->resolvePaths($input->getArgument('path'), $scopeId);

        $configWriter = $this->getConfigWriter();
        foreach ($paths as $path) {
            $deleted = array_merge($deleted, $this->_deletePath($input, $configWriter, $path, $scopeId));
        }

        if (count($deleted) > 0) {
            $this->getHelper('table')
                ->setHeaders(['deleted path', 'scope', 'id'])
                ->setRows($deleted)
                ->render($output);
        }

        return Command::SUCCESS;
    }

    /**
     *
     */
    private function resolvePaths($path, $scopeId)
    {
        if (false === strstr($path, '*')) {
            return (array) $path;
        }

        $paths = [];

        $collection = clone $this->collection;

        $searchPath = str_replace('*', '%', $path);
        $collection->addFieldToFilter('path', ['like' => $searchPath]);

        if ($scopeId) {
            $collection->addFieldToFilter('scope_id', $scopeId);
        }

        $collection->addOrder('path', 'ASC');

        foreach ($collection as $item) {
            $paths[] = $item->getPath();
        }

        $paths = array_unique($paths);

        return $paths;
    }

    /**
     * @param InputInterface $input
     * @param WriterInterface $configWriter
     * @param                $path
     * @param                $scopeId
     *
     * @return array
     */
    protected function _deletePath(
        InputInterface $input,
        WriterInterface $configWriter,
        $path,
        $scopeId
    ) {
        $deleted = [];
        if ($input->getOption('all')) {
            $storeManager = $this->getObjectManager()->get('Magento\Store\Model\StoreManager');

            // Delete default
            $this->delete($configWriter, $deleted, $path, 'default', 0);

            $deleted[] = [
                'path'    => $path,
                'scope'   => 'default',
                'scopeId' => 0,
            ];

            // Delete websites
            foreach ($storeManager->getWebsites() as $website) {
                $this->delete($configWriter, $deleted, $path, 'websites', $website->getId());
            }

            // Delete stores
            foreach ($storeManager->getStores() as $store) {
                $this->delete($configWriter, $deleted, $path, 'stores', $store->getId());
            }
        } else {
            foreach ($this->resolveScopeIds($path, $input->getOption('scope'), $scopeId) as $item) {
                $this->delete($configWriter, $deleted, $path, $item[1], $item[2]);
            }
        }

        return $deleted;
    }

    private function delete(WriterInterface $configWriter, &$deleted, $path, $scope, $scopeId)
    {
        $configWriter->delete($path, $scope, $scopeId);

        $deleted[] = [
            'path'    => $path,
            'scope'   => $scope,
            'scopeId' => $scopeId,
        ];
    }

    /**
     * @param string $path
     * @param string $scope
     * @param int|null $scopeId
     *
     * @return array
     */
    private function resolveScopeIds($path, $scope, $scopeId)
    {
        $result = [];

        if ($scopeId !== null) {
            $result[] = [$path, $scope, $scopeId];

            return $result;
        }

        $collection = clone $this->collection;

        $collection->addFieldToFilter('path', ['eq' => $path]);
        $collection->addFieldToFilter('scope', ['eq' => $scope]);
        $collection->addOrder('scope_id', 'ASC');

        $collection->clear();

        foreach ($collection as $item) {
            $result[] = [$item->getPath(), $item->getScope(), $item->getScopeId()];
        }

        return $result;
    }
}
