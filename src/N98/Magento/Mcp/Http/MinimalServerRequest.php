<?php

namespace N98\Magento\Mcp\Http;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

class MinimalServerRequest implements ServerRequestInterface
{
    private string $method;
    private UriInterface $uri;
    private array $headers = [];
    private StreamInterface $body;
    private array $serverParams;
    private array $queryParams = [];
    private $parsedBody = null;
    private array $attributes = [];
    private array $cookieParams = [];
    private array $uploadedFiles = [];
    private string $protocolVersion = '1.1';
    private string $requestTarget;

    public function __construct(string $method, $uri, array $headers = [], $body = null, string $version = '1.1', array $serverParams = [])
    {
        $this->method = $method;
        $this->uri = ($uri instanceof UriInterface) ? $uri : new MinimalUri($uri);
        $this->serverParams = $serverParams;
        $this->protocolVersion = $version;
        $this->requestTarget = (string) $uri;

        foreach ($headers as $name => $value) {
            $this->headers[strtolower($name)] = (array) $value;
        }

        if ($body instanceof StreamInterface) {
            $this->body = $body;
        } else {
            $this->body = new MinimalStream($body ?? '');
        }
    }

    public function getProtocolVersion(): string
    {
        return $this->protocolVersion;
    }
    public function withProtocolVersion(string $version): static
    {
        $new = clone $this;
        $new->protocolVersion = $version;
        return $new;
    }
    public function getHeaders(): array
    {
        return $this->headers;
    }
    public function hasHeader(string $name): bool
    {
        return isset($this->headers[strtolower($name)]);
    }
    public function getHeader(string $name): array
    {
        return $this->headers[strtolower($name)] ?? [];
    }
    public function getHeaderLine(string $name): string
    {
        return implode(', ', $this->getHeader($name));
    }
    public function withHeader(string $name, $value): static
    {
        $new = clone $this;
        $new->headers[strtolower($name)] = (array) $value;
        return $new;
    }
    public function withAddedHeader(string $name, $value): static
    {
        $new = clone $this;
        $new->headers[strtolower($name)] = array_merge($new->headers[strtolower($name)] ?? [], (array) $value);
        return $new;
    }
    public function withoutHeader(string $name): static
    {
        $new = clone $this;
        unset($new->headers[strtolower($name)]);
        return $new;
    }
    public function getBody(): StreamInterface
    {
        return $this->body;
    }
    public function withBody(StreamInterface $body): static
    {
        $new = clone $this;
        $new->body = $body;
        return $new;
    }

    public function getRequestTarget(): string
    {
        return $this->requestTarget;
    }
    public function withRequestTarget(string $requestTarget): static
    {
        $new = clone $this;
        $new->requestTarget = $requestTarget;
        return $new;
    }
    public function getMethod(): string
    {
        return $this->method;
    }
    public function withMethod(string $method): static
    {
        $new = clone $this;
        $new->method = $method;
        return $new;
    }
    public function getUri(): UriInterface
    {
        return $this->uri;
    }
    public function withUri(UriInterface $uri, bool $preserveHost = false): static
    {
        $new = clone $this;
        $new->uri = $uri;
        return $new;
    }

    public function getServerParams(): array
    {
        return $this->serverParams;
    }
    public function getCookieParams(): array
    {
        return $this->cookieParams;
    }
    public function withCookieParams(array $cookies): static
    {
        $new = clone $this;
        $new->cookieParams = $cookies;
        return $new;
    }
    public function getQueryParams(): array
    {
        return $this->queryParams;
    }
    public function withQueryParams(array $query): static
    {
        $new = clone $this;
        $new->queryParams = $query;
        return $new;
    }
    public function getUploadedFiles(): array
    {
        return $this->uploadedFiles;
    }
    public function withUploadedFiles(array $uploadedFiles): static
    {
        $new = clone $this;
        $new->uploadedFiles = $uploadedFiles;
        return $new;
    }
    public function getParsedBody()
    {
        return $this->parsedBody;
    }
    public function withParsedBody($data): static
    {
        $new = clone $this;
        $new->parsedBody = $data;
        return $new;
    }
    public function getAttributes(): array
    {
        return $this->attributes;
    }
    public function getAttribute(string $name, $default = null)
    {
        return $this->attributes[$name] ?? $default;
    }
    public function withAttribute(string $name, $value): static
    {
        $new = clone $this;
        $new->attributes[$name] = $value;
        return $new;
    }
    public function withoutAttribute(string $name): static
    {
        $new = clone $this;
        unset($new->attributes[$name]);
        return $new;
    }
}
