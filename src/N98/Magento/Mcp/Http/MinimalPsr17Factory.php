<?php

namespace N98\Magento\Mcp\Http;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;

class MinimalPsr17Factory implements ResponseFactoryInterface, StreamFactoryInterface, ServerRequestFactoryInterface
{
    public function createResponse(int $code = 200, string $reasonPhrase = ''): ResponseInterface
    {
        return new MinimalResponse($code, [], null, '1.1', $reasonPhrase);
    }

    public function createStream(string $content = ''): StreamInterface
    {
        return new MinimalStream($content);
    }

    public function createStreamFromFile(string $filename, string $mode = 'r'): StreamInterface
    {
        return new MinimalStream(fopen($filename, $mode));
    }

    public function createStreamFromResource($resource): StreamInterface
    {
        return new MinimalStream($resource);
    }

    public function createServerRequest(string $method, $uri, array $serverParams = []): ServerRequestInterface
    {
        return new MinimalServerRequest($method, $uri, [], null, '1.1', $serverParams);
    }
}
