<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace N98\Magento\Command\Admin\User;

use N98\Magento\Command\TestCase;

/**
 * @see \N98\Magento\Command\Admin\User\ChangeStatusCommand
 */
class ChangeStatusCommandTest extends TestCase
{
    private const USERNAME = 'admin';

    /**
     * @var array
     */
    private $originalRow;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalRow = $this->getDatabaseConnection()->fetchRow(
            'SELECT * FROM admin_user WHERE username = ?',
            [self::USERNAME]
        );

        if (!$this->originalRow) {
            $this->markTestSkipped('No admin user found in the test Magento installation.');
        }
    }

    protected function tearDown(): void
    {
        if ($this->originalRow) {
            $this->getDatabaseConnection()->update(
                'admin_user',
                $this->originalRow,
                ['user_id = ?' => $this->originalRow['user_id']]
            );
        }

        parent::tearDown();
    }

    /**
     * Regression test for https://github.com/netz98/n98-magerun2/issues/2084
     *
     * Repeated executions of admin:user:change-status must not touch admin_user.extra.
     */
    public function testChangeStatusDoesNotModifyExtraColumn()
    {
        $extra = '{"configState":{"section_general":1}}';
        $this->getDatabaseConnection()->update(
            'admin_user',
            ['extra' => $extra],
            ['user_id = ?' => $this->originalRow['user_id']]
        );

        $this->assertExecute(['command' => 'admin:user:change-status', '--activate' => true, 'user' => self::USERNAME]);
        $this->assertSame($extra, $this->fetchExtra(), 'extra must be unchanged after activating the user');
        $this->assertSame('1', $this->fetchIsActive(), 'user must be active');

        $this->assertExecute(['command' => 'admin:user:change-status', '--deactivate' => true, 'user' => self::USERNAME]);
        $this->assertSame($extra, $this->fetchExtra(), 'extra must be unchanged after deactivating the user');
        $this->assertSame('0', $this->fetchIsActive(), 'user must be inactive');

        // Run the same operation again to ensure it is idempotent regarding the extra column.
        $this->assertExecute(['command' => 'admin:user:change-status', '--deactivate' => true, 'user' => self::USERNAME]);
        $this->assertSame($extra, $this->fetchExtra(), 'extra must remain unchanged on repeated executions');
    }

    private function fetchExtra(): ?string
    {
        return $this->getDatabaseConnection()->fetchOne(
            'SELECT extra FROM admin_user WHERE username = ?',
            [self::USERNAME]
        );
    }

    private function fetchIsActive(): string
    {
        return (string) $this->getDatabaseConnection()->fetchOne(
            'SELECT is_active FROM admin_user WHERE username = ?',
            [self::USERNAME]
        );
    }
}
