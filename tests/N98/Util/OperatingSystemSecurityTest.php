<?php

namespace N98\Util;

use PHPUnit\Framework\TestCase;

class OperatingSystemSecurityTest extends TestCase
{
    /**
     * @requires OS Linux|Darwin
     */
    public function testLocateProgramCommandInjection()
    {
        if (OperatingSystem::isWindows()) {
            $this->markTestSkipped('Test only for POSIX systems');
        }

        // We try to inject a command that creates a file in the temporary directory
        $tmpFile = tempnam(sys_get_temp_dir(), 'injection_test');
        if (file_exists($tmpFile)) {
            unlink($tmpFile);
        }

        $injectedCommand = 'ls; touch ' . escapeshellarg($tmpFile);

        // This should not create the file if properly escaped
        OperatingSystem::locateProgram($injectedCommand);

        $exists = file_exists($tmpFile);
        if ($exists) {
            unlink($tmpFile);
        }

        $this->assertFalse($exists, 'Vulnerability: Command injection possible in locateProgram');
    }
}
