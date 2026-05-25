<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Developer\Theme;

use N98\Magento\Command\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class DuplicatesCommandTest extends TestCase
{
    public function testExecute()
    {
        $application = $this->getApplication();
        $application->add(new DuplicatesCommand());

        $command = $this->getApplication()->find('dev:theme:duplicates');

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command'       => $command->getName(),
            'theme'         => 'Magento/blank',
            'originalTheme' => 'Magento/blank',
        ]);

        $display = $commandTester->getDisplay();

        $this->assertStringContainsString('No duplicates were found', $display);
    }

    private function assertContainsPath($path, $haystack)
    {
        $segments = preg_split('~/~', $path);
        $separator = '([/\\\\])';
        $segmentCount = 0;
        $pattern = '~';
        while ($segment = array_shift($segments)) {
            $pattern .= preg_quote($segment, '~');
            if ($segments !== []) {
                $pattern .= $segmentCount++ !== 0 ? '\\1' : $separator;
            }
        }

        $pattern .= '~';

        $this->assertMatchesRegularExpression($pattern, $haystack);
    }
}
