<?php
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
class JUnitSessionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function creation()
    {
        $session = new JUnitSession("name");
        $this->assertInstanceOf(JUnitSession::class, $session);
        $this->assertSame('name', $session->getName());
        $this->assertSame(0, $session->save(null));
        $document = $session->getDocument();
        $this->assertInstanceOf(Document::class, $document);
        $this->assertSame($document, $session->getDocument());
        $this->assertFalse(@$session->save(null));
        $this->assertNotNull($session->addTestSuite());
        usleep(1000);
        $this->assertGreaterThan(0.001, $session->getDuration());
    }
}
