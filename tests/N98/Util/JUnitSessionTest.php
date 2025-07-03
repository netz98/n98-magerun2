<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

/*
 * @author Tom Klingenberg <https://github.com/ktomk>
 */

namespace N98\Util;

use N98\JUnitXml\Document;

/**
 * Class JUnitSessionTest
 *
 * @package N98\Util
 * @covers N98\Util\JUnitSession
 */
class JUnitSessionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function creation()
    {
        $session = new JUnitSession("name");
        $this->assertInstanceOf(JUnitSession::class, $session);
        $this->assertSame('name', $session->getName());
        $this->assertSame(0, $session->save('foo.xml'));
        $document = $session->getDocument();
        $this->assertInstanceOf(Document::class, $document);
        $this->assertSame($document, $session->getDocument());
        $saveResult = $session->save('foo.xml');
        $this->assertGreaterThan(0, $saveResult);
        $this->assertNotNull($session->addTestSuite());
        usleep(1000);
        $this->assertGreaterThan(0.001, $session->getDuration());
    }

    protected function tearDown(): void
    {
        if (is_file('foo.xml')) {
            unlink('foo.xml');
        }
    }
}
