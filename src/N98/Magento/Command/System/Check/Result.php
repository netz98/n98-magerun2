<?php

namespace N98\Magento\Command\System\Check;

use LogicException;

/**
 * Class Result
 * @package N98\Magento\Command\System\Check
 */
class Result
{
    /**
     * @var string
     */
    const STATUS_OK = 'ok';

    /**
     * @var string
     */
    const STATUS_ERROR = 'error';

    /**
     * @var string
     */
    const STATUS_WARNING = 'warning';

    /**
     * @var string
     */
    private $status;

    /**
     * @var string
     */
    private $message;

    /**
     * @var string
     */
    private $resultGroup;

    /**
     * @param string $status
     * @param string $message
     * @param string $resultGroup
     */
    public function __construct($status = self::STATUS_OK, $message = '', $resultGroup = '')
    {
        $this->setStatus($status);
        $this->message = $message;
        $this->resultGroup = $resultGroup;
    }

    /**
     * @return boolean
     */
    public function isValid()
    {
        return $this->status === self::STATUS_OK;
    }

    /**
     * @param boolean|string $status
     * @return $this
     */
    public function setStatus($status)
    {
        if (is_bool($status)) {
            $status = $status ? self::STATUS_OK : self::STATUS_ERROR;
        }

        if (!in_array($status, [self::STATUS_OK, self::STATUS_ERROR, self::STATUS_WARNING])) {
            throw new LogicException(
                'Wrong status was given. Use constants: Result::OK, Result::ERROR, Result::WARNING'
            );
        }

        $this->status = $status;

        return $this;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $message
     * @return $this
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @return string
     */
    public function getResultGroup()
    {
        return $this->resultGroup;
    }

    /**
     * @param string $resultGroup
     */
    public function setResultGroup($resultGroup)
    {
        $this->resultGroup = $resultGroup;
    }
}
