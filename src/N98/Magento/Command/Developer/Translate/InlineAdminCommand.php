<?php

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
     * @param StoreInterface $store
     * @param bool $disabled
     */
    protected function afterSave(StoreInterface $store, $disabled)
    {
        $this->detectAskAndSetDeveloperIp($store, $disabled);
    }
}
