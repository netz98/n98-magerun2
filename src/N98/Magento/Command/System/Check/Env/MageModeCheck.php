<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\System\Check\Env;

use N98\Magento\Command\System\Check\Result;

class MageModeCheck extends CheckAbstract
{
    /**
     * Check if MAGE_MODE has correct value (developer, production or default)
     */
    public function checkEnv()
    {
        $result = $this->_results->createResult();
        $mageMode = strtolower($this->_env['MAGE_MODE']);
        if (in_array($mageMode, ['developer', 'production', 'default'])) {
            $status = Result::STATUS_OK;
            $message = '<info><comment>MAGE_MODE</comment> has correct value.</info>';
        } else {
            $status = Result::STATUS_ERROR;
            $message = "<error><comment>MAGE_MODE</comment> has incorrect value. It should be either 'developer', 'production' or 'default'. Your value: <comment>$mageMode</comment></error>";
        }

        $result->setStatus($status);
        $result->setMessage($message);
    }
}
