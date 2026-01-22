<?php

namespace N98\Magento\Mcp\Http;

use Psr\Log\AbstractLogger;

class MinimalLogger extends AbstractLogger
{
    public function log($level, $message, array $context = [])
    {
        echo "[LOG] $level: $message " . json_encode($context) . "\n";
    }
}
