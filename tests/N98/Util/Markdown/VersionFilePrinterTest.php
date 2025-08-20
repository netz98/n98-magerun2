<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Util\Markdown;

class VersionFilePrinterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function itShouldReturnOnlyAPartForOldChangelogFormatWithoutDate()
    {
        $content = <<<CONTENT
RECENT CHANGES
==============

2.1.2
-----

* Update composer dependencies

2.1.1
-----

* lorem ipsum 
* Fix: lorem ipsum (by Foo Bar)
* lorem ipsum lorem ipsum lorem ipsum (by Peter)

2.1.0
-----

* lorem ipsum 

CONTENT;

        $expectedContent = <<<EXPECTED_CONTENT
RECENT CHANGES
==============

2.1.2
-----

* Update composer dependencies

EXPECTED_CONTENT;

        $sut = new VersionFilePrinter($content);

        $this->assertEquals($expectedContent, $sut->printFromVersion('2.1.1'));
    }

    /**
     * @test
     */
    public function itShouldReturnChangelogSectionWithDates()
    {
        $content = <<<CONTENT
RECENT CHANGES
==============

9.1.0 (unreleased)
------------------

- Add: --check option in dev:module:detect-composer-dependencies command (issue #1727)
- Fix: excluded tables were dumped as structure if --strip option was used (issue #1731)

9.0.2 (2025-07-21)
------------------

- Fix: db dump command hotfix release

9.0.1 (2025-06-24)
------------------

- fix: phar file had to re-create, due to a release issue
CONTENT;

        $expectedContent = <<<EXPECTED_CONTENT
RECENT CHANGES
==============

9.1.0 (unreleased)
------------------

- Add: --check option in dev:module:detect-composer-dependencies command (issue #1727)
- Fix: excluded tables were dumped as structure if --strip option was used (issue #1731)

EXPECTED_CONTENT;

        $sut = new VersionFilePrinter($content);
        $this->assertEquals(
            $expectedContent,
            $sut->printFromVersion('9.0.2')
        );
    }
}
