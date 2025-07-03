<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace N98\Magento\Command\Cache;

use Magento\Framework\App\CacheInterface;
use N98\Magento\Command\AbstractMagentoCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RemoveIdCommand extends AbstractMagentoCommand
{
    private CacheInterface $cache;

    protected function configure()
    {
        $this
            ->setName('cache:remove:id')
            ->setDescription('Remove cache entry by id')
            ->addArgument('id', InputArgument::REQUIRED, 'Cache id')
            ->addOption(
                'strict',
                null,
                InputOption::VALUE_NONE,
                'Use strict mode (remove only if cache id exists)',
            )
            ->setHelp(
                <<<'HELP'
Cache IDs can be listed by using the <comment>cache:report</comment> command.
The command is not checking if the cache id exists. If you want to check if the cache id exists
use the <comment>cache:remove:id</comment> command with the <comment>--strict</comment> option.

<comment>Example:</comment>
  - Clear controller action file list:
    <info>n98-magerun2.phar cache:remove:id app_action_list</info>
HELP
            );
    }

    /**
     * @param CacheInterface $cache
     * @return void
     */
    public function inject(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $cacheId = $input->getArgument('id');

        if ($input->getOption('strict')) {
            $cacheData = $this->cache->load($cacheId);
            if (!$cacheData) {
                $output->writeln('<error>Cache entry with id <comment>' . $cacheId . '</comment> does not exist.</error>');
                return self::FAILURE;
            }
        }

        $this->cache->remove($cacheId);

        $output->writeln('<info>Cache entry with id <comment>' . $cacheId . '</comment> was removed.</info>');

        return self::SUCCESS;
    }
}
