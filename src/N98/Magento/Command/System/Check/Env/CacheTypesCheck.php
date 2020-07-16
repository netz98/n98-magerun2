<?php

namespace N98\Magento\Command\System\Check\Env;

use N98\Magento\Command\System\Check\Result;

class CacheTypesCheck extends CheckAbstract
{
    /**
     * Test if cache_types have the right value '1' or '0'
     */
    public function checkEnv()
    {
        $success = true;
        foreach ($this->_env['cache_types'] as $cache_type) {
            if (!is_int($cache_type) || ($cache_type != 0 && $cache_type != 1)) {
                $success = false;
            }
        }

        $result = $this->_results->createResult();
        if ($success) {
            $status = Result::STATUS_OK;
            $message = '<info><comment>cache_types</comment> have correct values.</info>';
        } else {
            $status = Result::STATUS_ERROR;
            $message = "<error><comment>cache_types</comment> have incorrect values. It should have either '1' or '0' as value.</error>";
        }

        $result->setStatus($status);
        $result->setMessage($message);
    }
}
