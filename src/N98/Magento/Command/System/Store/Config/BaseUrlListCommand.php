<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\System\Store\Config;

use InvalidArgumentException;
use Magento\Backend\Setup\ConfigOptionsList as BackendConfigOptionsList;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use N98\Magento\Command\AbstractMagentoCommand;
use N98\Util\Console\Helper\Table\Renderer\RendererFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class BaseUrlListCommand
 * @package N98\Magento\Command\System\Store\Config
 */
class BaseUrlListCommand extends AbstractMagentoCommand
{
    /**
     * @var \Magento\Framework\Store\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\App\DeploymentConfig
     */
    private DeploymentConfig $deploymentConfig;

    protected function configure()
    {
        $this
            ->setName('sys:store:config:base-url:list')
            ->setDescription('Lists all base urls')
            ->addOption('with-admin-store', null, InputOption::VALUE_NONE, 'Include admin store')
            ->addOption('with-admin-login-url', null, InputOption::VALUE_NONE, 'Include admin login url')
            ->addOption(
                'format',
                null,
                InputOption::VALUE_OPTIONAL,
                'Output Format. One of [' . implode(',', RendererFactory::getFormats()) . ']'
            );
    }

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function inject(
        StoreManagerInterface $storeManager,
        DeploymentConfig $deploymentConfig
    ) {
        $this->storeManager = $storeManager;
        $this->deploymentConfig = $deploymentConfig;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // with-admin-login-url and with-admin-store cannot be set at the same time
        if ($input->getOption('with-admin-login-url') && $input->getOption('with-admin-store')) {
            throw new InvalidArgumentException(
                'You cannot use --with-admin-login-url and --with-admin-store at the same time.'
            );
        }

        $table = [];
        $this->detectMagento($output, true);

        if (!$input->getOption('format')) {
            $this->writeSection($output, 'Magento Stores - Base URLs');
        }
        $this->initMagento();

        foreach ($this->storeManager->getStores(true) as $store) {

            if ($store->getCode() == Store::ADMIN_CODE && $input->getOption('with-admin-login-url')) {
                $adminUri = $this->deploymentConfig->get(BackendConfigOptionsList::CONFIG_PATH_BACKEND_FRONTNAME);

                $table[] = [
                    $store->getId(),
                    $store->getCode(),
                    $store->getBaseUrl(UrlInterface::URL_TYPE_WEB) . $adminUri,
                    $store->getBaseUrl(UrlInterface::URL_TYPE_WEB, true) . $adminUri,
                ];
                continue;
            }

            if ($store->getCode() == Store::ADMIN_CODE && !$input->getOption('with-admin-store')) {
                continue;
            }

            $storeId = $store->getId();

            $table[$storeId] = [
                $storeId,
                $store->getCode(),
                $store->getBaseUrl(UrlInterface::URL_TYPE_WEB),
                $store->getBaseUrl(UrlInterface::URL_TYPE_WEB, true),
            ];
        }

        ksort($table);
        $this->getHelper('table')
            ->setHeaders(['id', 'code', 'unsecure_baseurl', 'secure_baseurl'])
            ->renderByFormat($output, $table, $input->getOption('format'));

        return Command::SUCCESS;
    }
}
