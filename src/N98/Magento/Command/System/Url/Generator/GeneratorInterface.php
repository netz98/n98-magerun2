<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace N98\Magento\Command\System\Url\Generator;

use Symfony\Component\Console\Output\OutputInterface;

interface GeneratorInterface
{
    /**
     * Regenerate URL rewrites for a specific entity
     *
     * @param array $entityIds
     * @param int $storeId
     * @param OutputInterface $output
     * @return int
     */
    public function regenerate(array $entityIds, int $storeId, OutputInterface $output): int;

    /**
     * Regenerate URL rewrites for all entities
     *
     * @param int $storeId
     * @param OutputInterface $output
     * @return int
     */
    public function regenerateAll(int $storeId, OutputInterface $output): int;
}
