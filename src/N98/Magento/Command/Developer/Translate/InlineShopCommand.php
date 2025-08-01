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

class InlineShopCommand extends AbstractMagentoStoreConfigCommand
{
    use TranslateFunctionsTrait;

    /**
     * @var string
     */
    protected $configPath = 'dev/translate_inline/active';

    /**
     * @var string
     */
    protected $toggleComment = 'Inline Translation';

    /**
     * @var string
     */
    protected $commandName = 'dev:translate:shop';

    /**
     * @var string
     */
    protected $commandDescription = 'Toggle inline translation tool for shop';

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
