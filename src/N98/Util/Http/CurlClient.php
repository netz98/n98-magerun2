<?php

namespace N98\Util\Http;

use RuntimeException;

/**
 * Utility class for making curl requests.
 */
class CurlClient
{
    /**
     * Make a GET request using curl.
     *
     * @param string $url The URL to request
     * @param array $headers Optional headers to send with the request
     * @param array $options Optional curl options
     * @return CurlHttpResponse Response object with success, statusCode, and body properties
     */
    public static function curlGet(string $url, array $headers = [], array $options = []): CurlHttpResponse
    {
        $response = new CurlHttpResponse();
        $ch = curl_init();

        // Set default options
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $options['connect_timeout'] ?? 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, $options['timeout'] ?? 30);

        // Disable timeout for large file downloads if specified
        if (isset($options['disable_timeout']) && $options['disable_timeout'] === true) {
            curl_setopt($ch, CURLOPT_TIMEOUT, 0); // 0 = no timeout
        }

        // Force HTTP/1.1 to avoid potential issues with HTTP/2
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

        // Set TCP keepalive to prevent connection drops
        if (defined('CURLOPT_TCP_KEEPALIVE')) {
            curl_setopt($ch, CURLOPT_TCP_KEEPALIVE, 1);
            curl_setopt($ch, CURLOPT_TCP_KEEPIDLE, 60);
            curl_setopt($ch, CURLOPT_TCP_KEEPINTVL, 30);
        }

        // Fail on HTTP error codes (4xx, 5xx)
        curl_setopt($ch, CURLOPT_FAILONERROR, true);

        // Set headers
        if (!empty($headers)) {
            $formattedHeaders = [];
            foreach ($headers as $key => $value) {
                $formattedHeaders[] = $key . ': ' . $value;
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, $formattedHeaders);
        }

        // Set output file if specified
        if (isset($options['filename'])) {
            // Open file in binary mode to ensure proper handling of binary data
            $fp = fopen($options['filename'], 'wb+');
            if (!$fp) {
                throw new RuntimeException("Failed to open output file: {$options['filename']}");
            }
            curl_setopt($ch, CURLOPT_FILE, $fp);

            // Set additional options for file downloads
            curl_setopt($ch, CURLOPT_BUFFERSIZE, 1024 * 1024); // 1MB buffer size for better performance with large files
            curl_setopt($ch, CURLOPT_BINARYTRANSFER, true); // Binary transfer mode

            // Resume download if file exists and has content
            if (filesize($options['filename']) > 0) {
                $resumeFrom = filesize($options['filename']);
                curl_setopt($ch, CURLOPT_RESUME_FROM, $resumeFrom);

                // Add Range header for better compatibility with some servers
                if (!isset($headers['Range'])) {
                    $headers['Range'] = 'bytes=' . $resumeFrom . '-';
                    $formattedHeaders[] = 'Range: bytes=' . $resumeFrom . '-';
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $formattedHeaders);
                }
            }
        }

        // Set progress callback if specified
        if (isset($options['progress_callback'])) {
            curl_setopt($ch, CURLOPT_NOPROGRESS, false);
            curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, $options['progress_callback']);
        }

        // Execute request
        $body = curl_exec($ch);
        $curlInfo = curl_getinfo($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        $curlErrno = curl_errno($ch);
        $success = $statusCode >= 200 && $statusCode < 300 && !$error;
        $contentLength = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);

        // Handle file output
        if (isset($options['filename']) && isset($fp)) {
            fclose($fp);

            // Validate file size if we're downloading to a file
            if ($success && isset($options['filename']) && file_exists($options['filename'])) {
                $actualFileSize = filesize($options['filename']);

                // If content length is known and doesn't match the file size, it's a partial download
                if ($contentLength > 0 && $actualFileSize != $contentLength) {
                    $success = false;
                    $error = "Partial download detected: expected $contentLength bytes, got $actualFileSize bytes";
                }

                // Store file size information in response
                $response->setFileSize($actualFileSize);
                $response->setExpectedSize($contentLength > 0 ? $contentLength : null);
            }
        }

        // Set response properties
        $response->setSuccess($success);
        $response->setStatusCode($statusCode);
        $response->setCurlErrno($curlErrno);
        if (!isset($options['filename'])) {
            $response->setBody($body);
        }
        if ($error) {
            $response->setError($error);
        }

        // Add detailed curl info for debugging
        $response->setCurlInfo($curlInfo);

        // Store specific curl info values directly
        $response->setContentLength(curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD));
        $response->setSizeDownload(curl_getinfo($ch, CURLINFO_SIZE_DOWNLOAD));

        curl_close($ch);
        return $response;
    }

    /**
     * Make a HEAD request using curl.
     *
     * @param string $url The URL to request
     * @param array $headers Optional headers to send with the request
     * @param array $options Optional curl options
     * @return CurlHttpResponse Response object with success, statusCode, and headers properties
     */
    public static function curlHead(string $url, array $headers = [], array $options = []): CurlHttpResponse
    {
        $response = new CurlHttpResponse();
        $ch = curl_init();

        // Set default options
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_NOBODY, true); // HEAD request
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $options['connect_timeout'] ?? 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, $options['timeout'] ?? 30);
        curl_setopt($ch, CURLOPT_HEADER, true);

        // Force HTTP/1.1 to avoid potential issues with HTTP/2
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

        // Set TCP keepalive to prevent connection drops
        if (defined('CURLOPT_TCP_KEEPALIVE')) {
            curl_setopt($ch, CURLOPT_TCP_KEEPALIVE, 1);
        }

        // Fail on HTTP error codes (4xx, 5xx)
        curl_setopt($ch, CURLOPT_FAILONERROR, true);

        // Set headers
        if (!empty($headers)) {
            $formattedHeaders = [];
            foreach ($headers as $key => $value) {
                $formattedHeaders[] = $key . ': ' . $value;
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, $formattedHeaders);
        }

        // Execute request
        $rawHeaders = curl_exec($ch);
        $curlInfo = curl_getinfo($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        $curlErrno = curl_errno($ch);
        $success = $statusCode >= 200 && $statusCode < 300 && !$error;

        // Parse headers
        $headerLines = explode("\r\n", $rawHeaders);
        $parsedHeaders = [];
        foreach ($headerLines as $line) {
            $parts = explode(':', $line, 2);
            if (count($parts) === 2) {
                $key = strtolower(trim($parts[0]));
                $value = trim($parts[1]);
                $parsedHeaders[$key] = $value;
            }
        }

        // Set response properties
        $response->setSuccess($success);
        $response->setStatusCode($statusCode);
        $response->setCurlErrno($curlErrno);
        $response->setHeaders($parsedHeaders);
        if ($error) {
            $response->setError($error);
        }

        // Add detailed curl info for debugging
        $response->setCurlInfo($curlInfo);

        curl_close($ch);
        return $response;
    }
}
