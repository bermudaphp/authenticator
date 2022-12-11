<?php

namespace App\Auth\Provider;

use Bermuda\Arrayable;
use Dflydev\FigCookies\FigResponseCookies;
use Dflydev\FigCookies\Modifier\SameSite;
use Dflydev\FigCookies\SetCookie;
use Psr\Http\Message\ResponseInterface;

final class CookieParams implements Arrayable
{
    public const name = 'name';
    public const domain = 'domain';
    public const path = 'path';
    public const sameSite = 'sameSite';
    public const httpOnly = 'httpOnly';
    public const secure = 'secure';

    public function __construct(
        public readonly string $name,
        public readonly string $domain,
        public readonly string $path,
        public readonly SameSite $sameSite,
        public readonly bool $httpOnly = true,
        public readonly bool $secure = true,
    ) {

    }

    /**
     * @param array $params
     * @return static
     */
    public static function createFromArray(array $params): self
    {
        if (!isset($params['name'])) {
            throw new \InvalidArgumentException(
                'Missing require element of name in params array'
            );
        }

        return new self(
            $params[self::name],
            $params[self::domain] ?? $_SERVER['HTTP_HOST'],
            $params[self::path] ?? '/',
            $params[self::sameSite] ?? SameSite::strict(),
            $params[self::httpOnly] ?? true,
            $params[self::secure] ?? true
        );
    }

    /**
     * @param string $value
     * @return $this
     */
    public function withDomain(string $value): self
    {
        return $this->replace(self::domain, $value);
    }

    /**
     * @param string $value
     * @return $this
     */
    public function withSecure(string $value): self
    {
        return $this->replace(self::secure, $value);
    }

    /**
     * @param bool $value
     * @return $this
     */
    public function withHttpOnly(bool $value): self
    {
        return $this->replace(self::httpOnly, $value);
    }

    /**
     * @param string $value
     * @return $this
     */
    public function withPath(string $value): self
    {
        return $this->replace(self::path, $value);
    }

    /**
     * @param SameSite $value
     * @return $this
     */
    public function withSameSite(SameSite $value): self
    {
        return $this->replace(self::sameSite, $value);
    }

    /**
     * @param string $name
     * @param string|bool $value
     * @return $this
     */
    private function replace(string $name, string|bool|SameSite $value): self
    {
        $params = $this->toArray();
        $params[$name] = $value;

        return self::createFromArray($params);
    }

    /**
     * @param string $name
     * @return static
     */
    public static function withDefaults(string $name): self
    {
        return new self($name, $_SERVER['HTTP_HOST'], '/', SameSite::strict(), true, true);
    }

    /**
     * @param string $value
     * @return SetCookie
     */
    public function setCookie(string $value): SetCookie
    {
        return SetCookie::create($this->name, $value)
            ->withDomain($this->domain)
            ->withSecure($this->secure)
            ->withSameSite($this->sameSite)
            ->withHttpOnly($this->httpOnly);
    }

    /**
     * @param ResponseInterface $response
     * @param string $value
     * @return ResponseInterface
     */
    public function write(ResponseInterface $response, string $value): ResponseInterface
    {
        return FigResponseCookies::set($response, $this->setCookie($value));
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return get_object_vars($this);
    }
}