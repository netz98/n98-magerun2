<?php

namespace N98\Magento\Command\Mcp\Server;

use PHPUnit\Framework\TestCase;

class StartCommandTest extends TestCase
{
    public function testConfigure()
    {
        $command = new StartCommand();
        $this->assertEquals('mcp:server:start', $command->getName());
        $this->assertStringContainsString('Start an MCP server', $command->getDescription());
    }
}
