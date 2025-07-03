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
 * Class TemplateHintsBlocksCommand
 * @package N98\Magento\Command\Developer
 */
class TemplateHintsBlocksCommand extends AbstractMagentoStoreConfigCommand
{
    /**
     * @var string
     */
    protected $commandName = 'dev:template-hints-blocks';

    /**
     * @var string
     */
    protected $commandDescription = 'Toggles template hints block names';

    /**
     * @var string
     */
    protected $configPath = 'dev/debug/template_hints_blocks';

    /**
     * @var string
     */
    protected $toggleComment = 'Template Hints Blocks';

    /**
     * @var string
     */
    protected $scope = self::SCOPE_STORE_VIEW;
}
