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
 * Class RunCommandTest
 * @package N98\Magento\Command\Script\Repository
 */
class RunCommandTest extends TestCase
{
    public function testExecute()
    {
        $application = $this->getApplication();
        $config = $application->getConfig();
        $config['script']['folders'][] = __DIR__ . '/_scripts';
        $application->setConfig($config);

        $input = [
            'command' => 'script:repo:run',
            'script'  => 'hello-world',
        ];

        // Runs sys:info -> Check for any output
        $this->assertDisplayContains($input, 'Vendors');
        $this->assertDisplayContains($input, 'Magento');
        $this->assertDisplayContains($input, __DIR__ . '/_scripts/hello-world.magerun');
    }
}
