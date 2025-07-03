<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\System\Check\Env;

use N98\Magento\Command\System\Check\Result;

class KeyExistsCheck extends CheckAbstract
{
    /**
     * Check if env keys exists
     */
    public function checkEnv()
    {
        $requiredKeys = $this->_commandConfig['env']['required-keys'];
        foreach ($requiredKeys as $value) {
            $this->envKeyExist($value);
        }
    }

    /**
     * Check if env key exists
     *
     * @param string $key
     */
    protected function envKeyExist($key)
    {
        $result = $this->_results->createResult();
        if ($this->_dot->has($key)) {
            $status = Result::STATUS_OK;
            $message = '<info>Key <comment>' . $key . '</comment> found.</info>';
        } else {
            $status = Result::STATUS_ERROR;
            $message = '<error>Key <comment>' . $key . '</comment> not found!</error>';
        }

        $result->setStatus($status);
        $result->setMessage($message);
    }
}
