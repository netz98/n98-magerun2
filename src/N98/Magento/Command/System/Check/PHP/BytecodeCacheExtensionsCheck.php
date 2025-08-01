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
 * Class BytecodeCacheExtensionsCheck
 * @package N98\Magento\Command\System\Check\PHP
 */
class BytecodeCacheExtensionsCheck implements SimpleCheck, CommandConfigAware
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
        $result = $results->createResult();

        $bytecopdeCacheExtensions = $this->_commandConfig['php']['bytecode-cache-extensions'];
        $bytecodeCacheExtensionLoaded = false;
        $bytecodeCacheExtension = null;
        foreach ($bytecopdeCacheExtensions as $ext) {
            if (extension_loaded($ext)) {
                $bytecodeCacheExtension = $ext;
                $bytecodeCacheExtensionLoaded = true;
                break;
            }
        }
        $result->setStatus($bytecodeCacheExtensionLoaded ? Result::STATUS_OK : Result::STATUS_WARNING);
        if ($result->isValid()) {
            $result->setMessage(
                "<info>Bytecode Cache <comment>$bytecodeCacheExtension</comment> found.</info>"
            );
        } else {
            $result->setMessage(
                "<error>No Bytecode-Cache found!</error> <comment>It's recommended to install anyone of " .
                implode(', ', $bytecopdeCacheExtensions) . '.</comment>'
            );
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
