<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Database;

use N98\Util\Console\Helper\DatabaseHelper;
use PHPUnit\Framework\TestCase;

class ConnectionPropertyDeprecationTest extends TestCase
{
    public function testConnectionPropertyAccessTriggersDeprecation()
    {
        $dbHelperMock = $this->getMockBuilder(DatabaseHelper::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getConnection'])
            ->getMock();

        $dbHelperMock->expects($this->once())
            ->method('getConnection')
            ->willReturn(new \stdClass()); // Return dummy connection

        $command = $this->getMockBuilder(AbstractDatabaseCommand::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getDatabaseHelper'])
            ->getMock();

        $command->expects($this->any())
            ->method('getDatabaseHelper')
            ->willReturn($dbHelperMock);

        $triggered = false;
        set_error_handler(function ($errno, $errstr) use (&$triggered) {
            if ($errno === E_USER_DEPRECATED) {
                if (strpos($errstr, 'Accessing the magic property "_connection" is deprecated') !== false) {
                    $triggered = true;
                    // Return true to suppress standard error handling (preventing PHPUnit from failing the test due to error)
                    return true;
                }
            }
            // Delegate to previous handler
            return false;
        });

        try {
            $connection = $command->_connection;
        } finally {
            restore_error_handler();
        }

        $this->assertTrue($triggered, 'Expected deprecation error was not triggered.');
        $this->assertInstanceOf(\stdClass::class, $connection);
    }
}
