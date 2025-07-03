<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\System\Check\PHP;

use N98\Magento\Command\CommandConfigAware;
use N98\Magento\Command\System\Check\Result;
use N98\Magento\Command\System\Check\ResultCollection;
use N98\Magento\Command\System\Check\SimpleCheck;

/**
 * Class ExtensionsCheck
 * @package N98\Magento\Command\System\Check\PHP
 */
class ExtensionsCheck implements SimpleCheck, CommandConfigAware
{
    /**
     * @var array
     */
    protected $_commandConfig;

    /**
     * @param ResultCollection $results
     */
    public function check(ResultCollection $results)
    {
        $requiredExtensions = $this->_commandConfig['php']['required-extensions'];

        foreach ($requiredExtensions as $ext) {
            $result = $results->createResult();
            $result->setStatus(extension_loaded($ext) ? Result::STATUS_OK : Result::STATUS_ERROR);
            if ($result->isValid()) {
                $result->setMessage("<info>Required PHP Module <comment>$ext</comment> found.</info>");
            } else {
                $result->setMessage("<error>Required PHP Module $ext not found!</error>");
            }
        }
    }

    /**
     * @param array $commandConfig
     */
    public function setCommandConfig(array $commandConfig)
    {
        $this->_commandConfig = $commandConfig;
    }
}
