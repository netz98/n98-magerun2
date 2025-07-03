<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Script\Repository;

use N98\Magento\Command\TestCase;

/**
 * Class ListCommandTest
 * @package N98\Magento\Command\Script\Repository
 */
class ListCommandTest extends TestCase
{
    public function testExecute()
    {
        $application = $this->getApplication();
        $config = $application->getConfig();
        $config['script']['folders'][] = __DIR__ . '/_scripts';
        $application->setConfig($config);

        $this->assertDisplayContains('script:repo:list', 'Cache Flush Command Test (Hello World)');
        $this->assertDisplayContains('script:repo:list', 'Foo command');
        $this->assertDisplayContains('script:repo:list', 'Bar command');
    }
}
