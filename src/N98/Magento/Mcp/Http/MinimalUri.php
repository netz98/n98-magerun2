<?php

namespace N98\Magento\Mcp\Http;

use Psr\Http\Message\UriInterface;

class MinimalUri implements UriInterface
{
    private string $scheme = '';
    private string $host = '';
    private ?int $port = null;
    private string $user = '';
    private string $pass = '';
    private string $path = '';
    private string $query = '';
    private string $fragment = '';

    public function __construct(string $uri = '')
    {
        $parts = parse_url($uri);
        if ($parts === false) {
            throw new \InvalidArgumentException("Invalid URI: $uri");
        }
        $this->scheme = $parts['scheme'] ?? '';
        $this->host = $parts['host'] ?? '';
        $this->port = $parts['port'] ?? null;
        $this->user = $parts['user'] ?? '';
        $this->pass = $parts['pass'] ?? '';
        $this->path = $parts['path'] ?? '';
        $this->query = $parts['query'] ?? '';
        $this->fragment = $parts['fragment'] ?? '';
    }

    public function getScheme(): string
    {
        return $this->scheme;
    }
    public function getAuthority(): string
    {
        $auth = $this->host;
        if ($this->port)
            $auth .= ':' . $this->port;
        if ($this->user)
            $auth = $this->user . ($this->pass ? ':' . $this->pass : '') . '@' . $auth;
        return $auth;
    }
    public function getUserInfo(): string
    {
        return $this->user . ($this->pass ? ':' . $this->pass : '');
    }
    public function getHost(): string
    {
        return $this->host;
    }
    public function getPort(): ?int
    {
        return $this->port;
    }
    public function getPath(): string
    {
        return $this->path;
    }
    public function getQuery(): string
    {
        return $this->query;
    }
    public function getFragment(): string
    {
        return $this->fragment;
    }
    public function withScheme($scheme): static
    {
        $new = clone $this;
        $new->scheme = $scheme;
        return $new;
    }
    public function withUserInfo($user, $password = null): static
    {
        $new = clone $this;
        $new->user = $user;
        $new->pass = $password;
        return $new;
    }
    public function withHost($host): static
    {
        $new = clone $this;
        $new->host = $host;
        return $new;
    }
    public function withPort($port): static
    {
        $new = clone $this;
        $new->port = $port;
        return $new;
    }
    public function withPath($path): static
    {
        $new = clone $this;
        $new->path = $path;
        return $new;
    }
    public function withQuery($query): static
    {
        $new = clone $this;
        $new->query = $query;
        return $new;
    }
    public function withFragment($fragment): static
    {
        $new = clone $this;
        $new->fragment = $fragment;
        return $new;
    }

    public function __toString(): string
    {
        return ($this->scheme ? $this->scheme . ':' : '') .
            ($this->getAuthority() ? '//' . $this->getAuthority() : '') .
            $this->path .
            ($this->query ? '?' . $this->query : '') .
            ($this->fragment ? '#' . $this->fragment : '');
    }
}
