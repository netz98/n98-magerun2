<?php

namespace N98\Magento\Mcp\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class MinimalResponse implements ResponseInterface
{
    private array $headers = [];
    private string $protocolVersion = '1.1';
    private StreamInterface $body;
    private int $statusCode;
    private string $reasonPhrase;

    public function __construct(int $statusCode = 200, array $headers = [], $body = null, string $version = '1.1', string $reason = '')
    {
        $this->statusCode = $statusCode;
        $this->reasonPhrase = $reason;
        $this->protocolVersion = $version;

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
        $name = strtolower($name);
        $new->headers[$name] = array_merge($new->headers[$name] ?? [], (array) $value);
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

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function withStatus(int $code, string $reasonPhrase = ''): static
    {
        $new = clone $this;
        $new->statusCode = $code;
        $new->reasonPhrase = $reasonPhrase;
        return $new;
    }

    public function getReasonPhrase(): string
    {
        return $this->reasonPhrase;
    }
}
