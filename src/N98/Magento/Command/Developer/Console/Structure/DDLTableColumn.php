<?php
/**
 * Copyright © 2016 netz98 new media GmbH. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace N98\Magento\Command\Developer\Console\Structure;

use Magento\Framework\DB\Ddl\Table;

class DDLTableColumn
{
    /**
     * @var array
     */
    private $intTypes = [
        Table::TYPE_BIGINT,
        Table::TYPE_INTEGER,
        Table::TYPE_SMALLINT,
    ];

    /**
     * @var array
     */
    private $columnTypesWithSize = [
        Table::TYPE_TEXT,
    ];

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $type;

    /**
     * @var bool
     */
    private $unsigned;

    /**
     * @var mixed
     */
    private $default;

    /**
     * @var bool
     */
    private $identity;

    /**
     * @var bool
     */
    private $primary;

    /**
     * @var int
     */
    private $size;

    /**
     * @var string
     */
    private $comment;

    /**
     * @var bool
     */
    private $nullable;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return null
     */
    public function getUnsigned()
    {
        return $this->unsigned;
    }

    /**
     * @param null $unsigned
     */
    public function setUnsigned($unsigned)
    {
        $this->unsigned = $unsigned;
    }

    /**
     * @return null
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * @param null $default
     */
    public function setDefault($default)
    {
        $this->default = $default;
    }

    /**
     * @return null
     */
    public function getIdentity()
    {
        return $this->identity;
    }

    /**
     * @param null $identity
     */
    public function setIdentity($identity)
    {
        $this->identity = $identity;
    }

    /**
     * @return null
     */
    public function getPrimary()
    {
        return $this->primary;
    }

    /**
     * @param null $primary
     */
    public function setPrimary($primary)
    {
        $this->primary = $primary;
    }

    /**
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @param int $size
     */
    public function setSize($size)
    {
        $this->size = $size;
    }

    /**
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param string $comment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
    }

    /**
     * @return boolean
     */
    public function isNullable()
    {
        return $this->nullable;
    }

    /**
     * @param boolean $nullable
     */
    public function setNullable($nullable)
    {
        $this->nullable = $nullable;
    }

    /**
     * @return bool
     */
    public function isIntType()
    {
        return in_array($this->getType(), $this->intTypes);
    }

    /**
     * @return bool
     */
    public function isTypeWithSize()
    {
        return in_array($this->getType(), $this->columnTypesWithSize);
    }

    /**
     * @return array
     */
    public function getDefinitionArray()
    {
        // @TODO
    }
}