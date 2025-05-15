<?php

namespace N98\Util\Http;

/**
 * Response object for curl HTTP requests.
 */
class CurlHttpResponse
{
    /**
     * Whether the request was successful.
     *
     * @var bool
     */
    private bool $success;

    /**
     * The HTTP status code.
     *
     * @var int
     */
    private int $statusCode;

    /**
     * The response body.
     *
     * @var string|null
     */
    private ?string $body = null;

    /**
     * The response headers.
     *
     * @var array
     */
    private array $headers = [];

    /**
     * The curl error message, if any.
     *
     * @var string|null
     */
    private ?string $error = null;

    /**
     * The curl error number, if any.
     *
     * @var int|null
     */
    private ?int $curlErrno = null;

    /**
     * The content length of the response.
     *
     * @var int|null
     */
    private ?int $contentLength = null;

    /**
     * The size of the downloaded content.
     *
     * @var int|null
     */
    private ?int $sizeDownload = null;

    /**
     * The file size of the downloaded file.
     *
     * @var int|null
     */
    private ?int $fileSize = null;

    /**
     * The expected size of the downloaded file.
     *
     * @var int|null
     */
    private ?int $expectedSize = null;

    /**
     * Detailed curl info for debugging.
     *
     * @var array|null
     */
    private ?array $curlInfo = null;

    /**
     * Magic method to check if a property exists.
     * This provides backward compatibility with direct property access.
     *
     * @param string $name The property name
     * @return bool Whether the property exists
     */
    public function __isset(string $name): bool
    {
        // Map snake_case property names to their camelCase equivalents
        $propertyMap = [
            'success' => 'success',
            'status_code' => 'statusCode',
            'body' => 'body',
            'headers' => 'headers',
            'error' => 'error',
            'curl_errno' => 'curlErrno',
            'content_length' => 'contentLength',
            'size_download' => 'sizeDownload',
            'file_size' => 'fileSize',
            'expected_size' => 'expectedSize',
            'curl_info' => 'curlInfo',
        ];

        // Check if the property exists in our map
        if (isset($propertyMap[$name])) {
            $camelCaseName = $propertyMap[$name];
            return isset($this->$camelCaseName);
        }

        return false;
    }

    /**
     * Magic method to get a property value.
     * This provides backward compatibility with direct property access.
     *
     * @param string $name The property name
     * @return mixed The property value
     */
    public function __get(string $name)
    {
        // Map snake_case property names to their getter methods
        $getterMap = [
            'success' => 'isSuccess',
            'status_code' => 'getStatusCode',
            'body' => 'getBody',
            'headers' => 'getHeaders',
            'error' => 'getError',
            'curl_errno' => 'getCurlErrno',
            'content_length' => 'getContentLength',
            'size_download' => 'getSizeDownload',
            'file_size' => 'getFileSize',
            'expected_size' => 'getExpectedSize',
            'curl_info' => 'getCurlInfo',
        ];

        // Call the appropriate getter method
        if (isset($getterMap[$name])) {
            $getter = $getterMap[$name];
            return $this->$getter();
        }

        // Property not found
        trigger_error("Undefined property: " . __CLASS__ . "::$name", E_USER_NOTICE);
        return null;
    }

    /**
     * Magic method to set a property value.
     * This provides backward compatibility with direct property access.
     *
     * @param string $name The property name
     * @param mixed $value The property value
     * @return void
     */
    public function __set(string $name, $value): void
    {
        // Map snake_case property names to their setter methods
        $setterMap = [
            'success' => 'setSuccess',
            'status_code' => 'setStatusCode',
            'body' => 'setBody',
            'headers' => 'setHeaders',
            'error' => 'setError',
            'curl_errno' => 'setCurlErrno',
            'content_length' => 'setContentLength',
            'size_download' => 'setSizeDownload',
            'file_size' => 'setFileSize',
            'expected_size' => 'setExpectedSize',
            'curl_info' => 'setCurlInfo',
        ];

        // Call the appropriate setter method
        if (isset($setterMap[$name])) {
            $setter = $setterMap[$name];
            $this->$setter($value);
            return;
        }

        // Property not found
        trigger_error("Undefined property: " . __CLASS__ . "::$name", E_USER_NOTICE);
    }

    /**
     * Get whether the request was successful.
     *
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->success;
    }

    /**
     * Set whether the request was successful.
     *
     * @param bool $success
     * @return self
     */
    public function setSuccess(bool $success): self
    {
        $this->success = $success;
        return $this;
    }

    /**
     * Get the HTTP status code.
     *
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Set the HTTP status code.
     *
     * @param int $statusCode
     * @return self
     */
    public function setStatusCode(int $statusCode): self
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    /**
     * Get the response body.
     *
     * @return string|null
     */
    public function getBody(): ?string
    {
        return $this->body;
    }

    /**
     * Set the response body.
     *
     * @param string|null $body
     * @return self
     */
    public function setBody(?string $body): self
    {
        $this->body = $body;
        return $this;
    }

    /**
     * Get the response headers.
     *
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Set the response headers.
     *
     * @param array $headers
     * @return self
     */
    public function setHeaders(array $headers): self
    {
        $this->headers = $headers;
        return $this;
    }

    /**
     * Get the curl error message.
     *
     * @return string|null
     */
    public function getError(): ?string
    {
        return $this->error;
    }

    /**
     * Set the curl error message.
     *
     * @param string|null $error
     * @return self
     */
    public function setError(?string $error): self
    {
        $this->error = $error;
        return $this;
    }

    /**
     * Get the curl error number.
     *
     * @return int|null
     */
    public function getCurlErrno(): ?int
    {
        return $this->curlErrno;
    }

    /**
     * Set the curl error number.
     *
     * @param int|null $curlErrno
     * @return self
     */
    public function setCurlErrno(?int $curlErrno): self
    {
        $this->curlErrno = $curlErrno;
        return $this;
    }

    /**
     * Get the content length of the response.
     *
     * @return int|null
     */
    public function getContentLength(): ?int
    {
        return $this->contentLength;
    }

    /**
     * Set the content length of the response.
     *
     * @param int|null $contentLength
     * @return self
     */
    public function setContentLength(?int $contentLength): self
    {
        $this->contentLength = $contentLength;
        return $this;
    }

    /**
     * Get the size of the downloaded content.
     *
     * @return int|null
     */
    public function getSizeDownload(): ?int
    {
        return $this->sizeDownload;
    }

    /**
     * Set the size of the downloaded content.
     *
     * @param int|null $sizeDownload
     * @return self
     */
    public function setSizeDownload(?int $sizeDownload): self
    {
        $this->sizeDownload = $sizeDownload;
        return $this;
    }

    /**
     * Get the file size of the downloaded file.
     *
     * @return int|null
     */
    public function getFileSize(): ?int
    {
        return $this->fileSize;
    }

    /**
     * Set the file size of the downloaded file.
     *
     * @param int|null $fileSize
     * @return self
     */
    public function setFileSize(?int $fileSize): self
    {
        $this->fileSize = $fileSize;
        return $this;
    }

    /**
     * Get the expected size of the downloaded file.
     *
     * @return int|null
     */
    public function getExpectedSize(): ?int
    {
        return $this->expectedSize;
    }

    /**
     * Set the expected size of the downloaded file.
     *
     * @param int|null $expectedSize
     * @return self
     */
    public function setExpectedSize(?int $expectedSize): self
    {
        $this->expectedSize = $expectedSize;
        return $this;
    }

    /**
     * Get detailed curl info for debugging.
     *
     * @return array|null
     */
    public function getCurlInfo(): ?array
    {
        return $this->curlInfo;
    }

    /**
     * Set detailed curl info for debugging.
     *
     * @param array|null $curlInfo
     * @return self
     */
    public function setCurlInfo(?array $curlInfo): self
    {
        $this->curlInfo = $curlInfo;
        return $this;
    }
}
