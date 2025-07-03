<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\System\Url\Generator;

use Magento\Store\Model\StoreManagerInterface;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractGenerator implements GeneratorInterface
{
    /**
     * @var UrlPersistInterface
     */
    protected $urlPersist;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var int
     */
    protected $batchSize = 100;

    /**
     * @var ProgressBar
     */
    protected $progressBar;

    /**
     * AbstractGenerator constructor.
     *
     * @param UrlPersistInterface $urlPersist
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        UrlPersistInterface $urlPersist,
        StoreManagerInterface $storeManager
    ) {
        $this->urlPersist = $urlPersist;
        $this->storeManager = $storeManager;
    }

    /**
     * Set batch size for pagination
     *
     * @param int $batchSize
     * @return $this
     */
    public function setBatchSize(int $batchSize)
    {
        $this->batchSize = $batchSize;
        return $this;
    }

    /**
     * Get batch size
     *
     * @return int
     */
    public function getBatchSize(): int
    {
        return $this->batchSize;
    }

    /**
     * Check if verbose output is enabled
     *
     * @param OutputInterface $output
     * @return bool
     */
    protected function isVerbose(OutputInterface $output): bool
    {
        return $output->isVerbose() || $output->isVeryVerbose() || $output->isDebug();
    }

    /**
     * Create a progress bar
     *
     * @param OutputInterface $output
     * @param int $max
     * @return ProgressBar
     */
    protected function createProgressBar(OutputInterface $output, int $max): ProgressBar
    {
        $this->progressBar = new ProgressBar($output, $max);
        $this->progressBar->setFormat(
            '%current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%'
        );
        return $this->progressBar;
    }

    /**
     * Write message only in verbose mode
     *
     * @param OutputInterface $output
     * @param string $message
     */
    protected function writeVerbose(OutputInterface $output, string $message): void
    {
        if ($this->isVerbose($output)) {
            $output->writeln($message);
        }
    }

    /**
     * Regenerate URL rewrites for a specific entity
     *
     * @param array $entityIds
     * @param int $storeId
     * @param OutputInterface $output
     * @return int
     */
    abstract public function regenerate(array $entityIds, int $storeId, OutputInterface $output): int;

    /**
     * Regenerate URL rewrites for all entities
     *
     * @param int $storeId
     * @param OutputInterface $output
     * @return int
     */
    abstract public function regenerateAll(int $storeId, OutputInterface $output): int;
}
