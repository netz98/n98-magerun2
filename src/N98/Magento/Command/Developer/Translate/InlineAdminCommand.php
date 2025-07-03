<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Developer\Translate;

use Magento\Store\Api\Data\StoreInterface;
use N98\Magento\Command\AbstractMagentoStoreConfigCommand;

class InlineAdminCommand extends AbstractMagentoStoreConfigCommand
{
    use TranslateFunctionsTrait;

    /**
     * @var string
     */
    protected $configPath = 'dev/translate_inline/active_admin';

    /**
     * @var string
     */
    protected $toggleComment = 'Inline Translation (Admin)';

    /**
     * @var string
     */
    protected $commandName = 'dev:translate:admin';

    /**
     * @var string
     */
    protected $commandDescription = 'Toggle inline translation tool for admin';

    /**
     * @var string
     */
    protected $scope = self::SCOPE_GLOBAL;

    /**
     * If required, handle the output and possible change of the developer IP restrictions
     *
     * @param \Magento\Store\Api\Data\StoreInterface $store
     * @param bool $disabled
     */
    protected function afterSave(StoreInterface $store, $disabled)
    {
        $this->detectAskAndSetDeveloperIp($store, $disabled);
    }
}
