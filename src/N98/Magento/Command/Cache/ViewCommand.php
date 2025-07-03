<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Cache;

use Magento\Framework\App\CacheInterface;
use Magento\PageCache\Model\Cache\Type as FullPageCache;
use N98\Magento\Command\AbstractMagentoCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\Exception\RuntimeException;

class ViewCommand extends AbstractMagentoCommand
{
    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var FullPageCache
     */
    private $fpc;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    private $encryptor;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    private $serializer;

    /**
     * @param CacheInterface $cache
     * @param FullPageCache $fpc
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     * @return void
     */
    public function inject(
        CacheInterface $cache,
        FullPageCache $fpc,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Framework\Serialize\SerializerInterface $serializer
    ) {
        $this->cache = $cache;
        $this->fpc = $fpc;
        $this->encryptor = $encryptor;
        $this->serializer = $serializer;
    }

    /**
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('cache:view')
            ->addArgument(
                'id',
                InputArgument::REQUIRED,
                'Cache-ID'
            )
            ->addOption(
                'fpc',
                null,
                InputOption::VALUE_NONE,
                'Use full page cache instead of core cache'
            )
            ->addOption(
                'unserialize',
                null,
                InputOption::VALUE_NONE,
                'Unserialize output'
            )
            ->addOption(
                'decrypt',
                null,
                InputOption::VALUE_NONE,
                'Decrypt output with encryption key'
            )
            ->setDescription('Prints a cache entry');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws RuntimeException
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectMagento($output, true);
        if (!$this->initMagento()) {
            return Command::FAILURE;
        }

        /** CacheInterface|FullPageCache $cacheInstance */
        if ($input->hasOption('fpc') && $input->getOption('fpc')) {
            $cacheInstance = $this->fpc;
        } else {
            $cacheInstance = $this->cache;
        }

        $cacheId = $input->getArgument('id');
        $cacheData = $cacheInstance->load($cacheId);
        if ($cacheData === false) {
            $output->writeln('Cache id <info>' . $cacheId . '</info> does not exist (anymore)');
            return Command::FAILURE;
        }

        if ($input->getOption('unserialize') && !$input->getOption('decrypt')) {
            $cacheData = $this->decorateSerialized($cacheData);
        }

        if ($input->getOption('decrypt')) {
            $cacheData = $this->decorateDecrypt($cacheData);
            if ($input->getOption('unserialize')) {
                $cacheData = $this->decorateSerialized($cacheData);
            }
        }

        $output->writeln($cacheData);

        return Command::SUCCESS;
    }

    /**
     * @param string $serialized
     * @return string
     */
    private function decorateSerialized($serialized)
    {
        try {
            $unserialized = $this->serializer->unserialize($serialized);
        } catch (\Exception $e) {
            $unserialized = \unserialize($serialized, ['allowed_classes' => false]);
        }

        if ($unserialized === false) {
            $buffer = $serialized;
        } else {
            try {
                $buffer = json_encode($unserialized, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
            } catch (\JsonException $e) {
                throw new RuntimeException(
                    'Unserialized failed. Try without --unserialize option.',
                    0,
                    $e
                );
            }
        }

        return $buffer;
    }

    /**
     * @param string $encrypted
     * @return string
     */
    private function decorateDecrypt($encrypted)
    {
        return $this->encryptor->decrypt($encrypted);
    }
}
