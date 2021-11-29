<?php
/**
 * @copyright Copyright (c) netz98 GmbH (https://www.netz98.de)
 *
 * @see PROJECT_LICENSE.txt
 */

declare(strict_types=1);

namespace N98\Magento\CoreCommand;

use Magento\Framework\App\ProductMetadataInterface;
use N98\Magento\Command\TestCase;

abstract class AbstractMagentoCoreCommandTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->registerCoreCommands();
    }

    /**
     * @param string $version
     * @return bool
     * @throws \Exception
     */
    protected function isMinimumMagentoVersion(string $version): bool
    {
        $productMetadata = $this->getApplication()
            ->getObjectManager()
            ->get(ProductMetadataInterface::class);

        return version_compare($version, $productMetadata->getVersion(), '<');
    }
}
