<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Design;

use N98\Magento\Command\AbstractMagentoStoreConfigCommand;

/**
 * Class DemoNoticeCommand
 * @package N98\Magento\Command\Design
 */
class DemoNoticeCommand extends AbstractMagentoStoreConfigCommand
{
    /**
     * @var string
     */
    protected $commandName = 'design:demo-notice';

    /**
     * @var string
     */
    protected $commandDescription = 'Toggles demo store notice for a store view';

    /**
     * @var string
     */
    protected $toggleComment = 'Demo Notice';

    /**
     * @var string
     */
    protected $configPath = 'design/head/demonotice';

    /**
     * @var string
     */
    protected $scope = self::SCOPE_STORE_VIEW_GLOBAL;
}
