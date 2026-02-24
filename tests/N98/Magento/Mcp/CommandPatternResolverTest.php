<?php

namespace N98\Magento\Mcp;

use PHPUnit\Framework\TestCase;
use RuntimeException;

class CommandPatternResolverTest extends TestCase
{
    public function testGetCommandGroupDefinitions()
    {
        $resolver = new CommandPatternResolver();

        $definitions = $resolver->getCommandGroupDefinitions([
            'command-groups' => [
                [
                    'id' => 'maintenance',
                    'description' => 'Maintenance commands',
                    'commands' => 'sys:maintenance sys:check',
                ],
                [
                    'id' => 'cron',
                    'commands' => ['sys:cron:*', 'index:*'],
                ],
            ],
        ]);

        $this->assertArrayHasKey('maintenance', $definitions);
        $this->assertArrayHasKey('cron', $definitions);
        $this->assertSame(['sys:maintenance', 'sys:check'], $definitions['maintenance']['commands']);
        $this->assertSame(['sys:cron:*', 'index:*'], $definitions['cron']['commands']);
    }

    public function testResolvePatternsWithGroupsAndNestedGroups()
    {
        $resolver = new CommandPatternResolver();
        $definitions = $resolver->getCommandGroupDefinitions([
            'command-groups' => [
                ['id' => 'cron', 'commands' => 'sys:cron:*'],
                ['id' => 'maintenance', 'commands' => '@cron sys:maintenance'],
            ],
        ]);

        $resolved = $resolver->resolvePatterns(['@maintenance', 'cache:*'], $definitions);

        $this->assertSame(['cache:*', 'sys:cron:*', 'sys:maintenance'], $resolved);
    }

    public function testResolvePatternsThrowsOnUnknownGroup()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Command-groups could not be resolved: @unknown');

        $resolver = new CommandPatternResolver();
        $resolver->resolvePatterns(['@unknown'], []);
    }
}
