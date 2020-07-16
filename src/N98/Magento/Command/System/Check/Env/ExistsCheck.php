<?php

namespace N98\Magento\Command\System\Check\Env;

use N98\Magento\Command\System\Check\Result;

class ExistsCheck extends CheckAbstract
{
    /**
     * Check if env.php exists
     */
    public function checkEnv()
    {
        $result = $this->_results->createResult();
        if (file_exists($this->_envFilePath)) {
            $status = Result::STATUS_OK;
            $message = '<info>Env file <comment>app/etc/env.php</comment> found!</info>';
        } else {
            $status = Result::STATUS_ERROR;
            $message = '<error>Env file <comment>app/etc/env.php</comment> not found!</error>';
        }

        $result->setStatus($status);
        $result->setMessage($message);
    }
}
