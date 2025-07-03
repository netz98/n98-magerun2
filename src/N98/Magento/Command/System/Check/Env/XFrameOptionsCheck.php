<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\System\Check\Env;

use N98\Magento\Command\System\Check\Result;

class XFrameOptionsCheck extends CheckAbstract
{
    /**
     * Check if x-frame-options has correct value
     */
    public function checkEnv()
    {
        $result = $this->_results->createResult();
        $xFrameOptions = strtolower($this->_env['x-frame-options']);
        if (in_array($xFrameOptions, ['deny', 'sameorigin']) || strpos($xFrameOptions, 'allow-from') !== false || $xFrameOptions == '*') {
            $status = Result::STATUS_OK;
            $message = '<info><comment>x-frame-options</comment> has correct value.</info>';
        } else {
            $status = Result::STATUS_ERROR;
            $message = "<error><comment>x-frame-options</comment> has incorrect value. It should be either 'deny', 'sameorigin', '*' or 'allow-from https://hostname'.</error>";
        }

        $result->setStatus($status);
        $result->setMessage($message);
    }
}
