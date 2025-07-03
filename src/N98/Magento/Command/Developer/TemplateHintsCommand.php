<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Developer;

use N98\Magento\Command\AbstractMagentoStoreConfigCommand;

/**
 * Class TemplateHintsCommand
 * @package N98\Magento\Command\Developer
 */
class TemplateHintsCommand extends AbstractMagentoStoreConfigCommand
{
    /**
     * @var string
     */
    protected $commandName = 'dev:template-hints';

    /**
     * @var string
     */
    protected $commandDescription = 'Toggles template hints';

    /**
     * @var string
     */
    protected $toggleComment = 'Template Hints';

    /**
     * @var string
     */
    protected $configPath = 'dev/debug/template_hints_storefront';

    /**
     * @var string
     */
    protected $scope = self::SCOPE_STORE_VIEW;
}
