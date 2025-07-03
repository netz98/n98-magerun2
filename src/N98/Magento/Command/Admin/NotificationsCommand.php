<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Admin;

use N98\Magento\Command\AbstractMagentoStoreConfigCommand;

class NotificationsCommand extends AbstractMagentoStoreConfigCommand
{
    /**
     * @var string
     */
    protected $commandName = 'admin:notifications';

    /**
     * @var string
     */
    protected $commandDescription = 'Toggles admin notifications';

    /**
     * @var string
     */
    protected $toggleComment = 'Admin Notifications';

    /**
     * @var string
     */
    protected $configPath = 'advanced/modules_disable_output/Magento_AdminNotification';

    /**
     * @var string
     */
    protected $trueName = 'hidden';

    /**
     * @var string
     */
    protected $falseName = 'visible';

    /**
     * @var string
     */
    protected $scope = self::SCOPE_GLOBAL;
}
