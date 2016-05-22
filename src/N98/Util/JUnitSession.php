<?php
/*
 * @author Tom Klingenberg <https://github.com/ktomk>
 */

namespace N98\Util;

use N98\JUnitXml\Document as JUnitXmlDocument;
use N98\JUnitXml\TestSuiteElement;

/**
 * Helper class as companion for JUnitXmlDocument based logging
 *
 * @see JUnitXmlDocument
 */
class JUnitSession
{
    /**
     * @var JUnitXmlDocument
     */
    private $document;

    /**
     * @var float
     */
    private $starTime;
    private $stopTime;

    /**
     * @var string
     */
    private $name;

    public function __construct($name)
    {
        $this->starTime = microtime(true);
        $this->name = $name;
    }

    /**
     * getter for JUnitXmlDocument associated wit this session
     *
     * @return JUnitXmlDocument
     */
    public function getDocument()
    {
        if (!$this->document) {
            $this->document = new JUnitXmlDocument();
        }

        return $this->document;
    }

    public function getName()
    {
        return $this->name;
    }

    /**
     * @return float
     */
    public function getDuration()
    {
        if (null === $this->stopTime) {
            $this->stopTime = microtime(true);
        }

        return $this->stopTime - $this->starTime;
    }

    /**
     * @return TestSuiteElement
     */
    public function addTestSuite()
    {
        return $this->getDocument()->addTestSuite();
    }

    /**
     * @param string $path
     * @return int|false the number of bytes written or false if an error occured
     */
    public function save($path)
    {
        if (!$this->document) {
            return 0;
        }

        return $this->document->save($path);
    }
}
