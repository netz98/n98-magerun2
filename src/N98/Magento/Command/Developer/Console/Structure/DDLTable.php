<?php
/**
 * Copyright © 2016 netz98 new media GmbH. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace N98\Magento\Command\Developer\Console\Structure;

class DDLTable
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $comment;

    /**
     * @var array|DDLTableColumn[]
     */
    private $columnDefinitions;

    /**
     * DDLTable constructor.
     * @param string $tableName
     * @param string $tableComment
     * @param DDLTableColumn[] $columnDefinitions
     */
    public function __construct($tableName = null, $tableComment = null, array $columnDefinitions = [])
    {
        $this->name = $tableName;
        $this->comment = $tableComment;
        $this->columnDefinitions = $columnDefinitions;
    }

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
     * @return array|DDLTableColumn[]
     */
    public function getColumnDefinitions()
    {
        return $this->columnDefinitions;
    }

    /**
     * @param array|DDLTableColumn[] $columnDefinitions
     */
    public function setColumnDefinitions($columnDefinitions)
    {
        $this->columnDefinitions = $columnDefinitions;
    }
}
