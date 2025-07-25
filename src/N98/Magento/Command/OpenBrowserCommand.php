<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command;

use Magento\Backend\Model\UrlInterface as BackendUrlInterface;
use Magento\Framework\UrlInterface as FrontendUrlInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\Store;
use N98\Util\Console\Helper\ParameterHelper;
use N98\Util\Exec;
use N98\Util\OperatingSystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class OpenBrowserCommand
 * @package N98\Magento\Command
 */
class OpenBrowserCommand extends AbstractMagentoCommand
{
    protected function configure()
    {
        $this
            ->setName('open-browser')
            ->addArgument('store', InputArgument::OPTIONAL, 'Store code or ID')
            ->setDescription('Open current project in browser');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws RuntimeException
     * @throws \Exception
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectMagento($output);
        if (!$this->initMagento()) {
            return Command::FAILURE;
        }

        /** @var $parameter ParameterHelper */
        $parameter = $this->getHelper('parameter');
        $store = $parameter->askStore($input, $output, 'store', true);

        if ($store->getId() === Store::DEFAULT_STORE_ID) {
            $url = $this->getBackendStoreUrl($store);
        } else {
            $url = $this->getFrontendStoreUrl($store);
        }

        $output->writeln('Opening URL <comment>' . $url . '</comment> in browser');

        $opener = $this->resolveOpenerCommand($output);
        Exec::run(escapeshellcmd($opener . ' ' . $url));

        return Command::SUCCESS;
    }

    /**
     * @param StoreInterface $store
     * @return string
     */
    private function getBackendStoreUrl(StoreInterface $store)
    {
        $baseConfig = $this->getHelper('magento')->getBaseConfig();

        if (!isset($baseConfig['backend']['frontName'])) {
            throw new RuntimeException('frontName for admin area could not be found.');
        }
        $adminFrontName = $baseConfig['backend']['frontName'];

        return rtrim($store->getBaseUrl(BackendUrlInterface::URL_TYPE_WEB), '/') . '/' . $adminFrontName;
    }

    /**
     * @param StoreInterface $store
     * @return string
     */
    private function getFrontendStoreUrl(StoreInterface $store)
    {
        return $store->getBaseUrl(FrontendUrlInterface::URL_TYPE_LINK) . '?___store=' . $store->getCode();
    }

    /**
     * @param OutputInterface $output
     * @return string
     */
    private function resolveOpenerCommand(OutputInterface $output)
    {
        $opener = '';
        if (OperatingSystem::isMacOs()) {
            $opener = 'open';
        } elseif (OperatingSystem::isWindows()) {
            $opener = 'start';
        } elseif (exec('which xdg-open')) {
            $opener = 'xdg-open';
        } elseif (exec('which gnome-open')) {
            $opener = 'gnome-open';
        } elseif (exec('which kde-open')) {
            $opener = 'kde-open';
        }

        if (empty($opener)) {
            throw new RuntimeException('No opener command like xdg-open, gnome-open, kde-open was found.');
        }

        if (OutputInterface::VERBOSITY_DEBUG <= $output->getVerbosity()) {
            $message = sprintf('open command is "%s"', $opener);
            $output->writeln(
                '<debug>' . $message . '</debug>'
            );
        }

        return $opener;
    }
}
