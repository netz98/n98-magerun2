<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Util\Http;

use RuntimeException;
use Throwable;

/**
 * Exception thrown during download operations with a reference to the remote URL.
 */
class DownloadException extends RuntimeException
{
    /**
     * The remote URL that was being downloaded.
     *
     * @var string
     */
    public string $remoteUrl;

    /**
     * Create a new download exception.
     *
     * @param string $message The exception message
     * @param string $remoteUrl The remote URL that was being downloaded
     * @param int $code The exception code
     * @param \Throwable|null $previous The previous throwable used for exception chaining
     */
    public function __construct(string $message, string $remoteUrl, int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->remoteUrl = $remoteUrl;
    }

    /**
     * Get the remote URL that was being downloaded.
     *
     * @return string
     */
    public function getRemoteUrl(): string
    {
        return $this->remoteUrl;
    }
}
