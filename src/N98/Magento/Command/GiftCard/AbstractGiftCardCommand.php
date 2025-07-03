<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\GiftCard;

use Magento\GiftCardAccount\Model\Giftcardaccount;
use N98\Magento\Command\AbstractMagentoCommand;

/**
 * Class AbstractGiftCardCommand
 *
 * @package N98\Magento\Command\GiftCard
 */
abstract class AbstractGiftCardCommand extends AbstractMagentoCommand
{
    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->getApplication()->isMagentoEnterprise();
    }

    /**
     * Get the gift card model, optionally loading from an ID
     * @param  string|null $code
     * @return Giftcardaccount
     */
    public function getGiftcard($code = null)
    {
        // Giftcardaccount is part of Adobe Commerce -> no completion here
        $giftcard = $this->getObjectManager()->get(Giftcardaccount::class); // @phpstan-ignore-line
        if ($code !== null) {
            $giftcard->loadByCode($code);
        }
        return $giftcard;
    }

    /**
     * Required to avoid "Area code not set" exceptions from Mage framework
     */
    public function setAdminArea()
    {
        $appState = $this->getObjectManager()->get(\Magento\Framework\App\State::class);
        $appState->setAreaCode('adminhtml');
    }
}
