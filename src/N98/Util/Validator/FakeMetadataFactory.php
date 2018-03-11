<?php

namespace N98\Util\Validator;

use Symfony\Component\Validator\Exception\NoSuchMetadataException;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\MetadataFactoryInterface;

class FakeMetadataFactory implements MetadataFactoryInterface
{
    /**
     * @var array
     */
    protected $metadatas = [];

    public function getMetadataFor($class)
    {
        if (is_object($class)) {
            $class = get_class($class);
        }

        if (!is_string($class)) {
            throw new NoSuchMetadataException('No metadata for type ' . gettype($class));
        }

        if (!isset($this->metadatas[$class])) {
            throw new NoSuchMetadataException('No metadata for "' . $class . '"');
        }

        return $this->metadatas[$class];
    }

    /**
     * @param mixed $class
     * @return bool
     */
    public function hasMetadataFor($class)
    {
        if (is_object($class)) {
            $class = get_class($class);
        }

        if (!is_string($class)) {
            return false;
        }

        return isset($this->metadatas[$class]);
    }

    /**
     * @param \Symfony\Component\Validator\Mapping\ClassMetadata $metadata
     */
    public function addMetadata(ClassMetadata $metadata)
    {
        $this->metadatas[$metadata->getClassName()] = $metadata;
    }
}
