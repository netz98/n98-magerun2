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
    public function itShouldReturnOnlyAPart()
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
}
