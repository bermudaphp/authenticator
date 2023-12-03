<?php

namespace Bermuda\Authenticator\Provider;

use Bermuda\Authenticator\AuthenticationException;
use Bermuda\Authenticator\AuthenticationProvider;
use Bermuda\Authenticator\Provider\Sessions\Sessionable;
use Bermuda\Authenticator\User\UserInterface;
use Bermuda\Authenticator\User\UserProviderInterface;
use Bermuda\HTTP\Cookie\CookieParams;
use Bermuda\HTTP\Headers\Header;
use Dflydev\FigCookies\Cookies;
use Dflydev\FigCookies\FigResponseCookies;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class CookieProvider implements AuthenticationProvider
{
    public function __construct(
        private readonly UserProviderInterface $userProvider,
        private readonly CookieParams $cookieParams,
    ) {
    }

    /**
     * @param ContainerInterface $container
     * @return static
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public static function fromContainer(ContainerInterface $container): self
    {
        $config = $container->get('config')['auth'];
        return new self($container->get(UserProviderInterface::class),
            CookieParams::createFromArray($config['cookie'][CookieProvider::class])
        );
    }

    /**
     * @throws AuthenticationException
     */
    public function authentication(ServerRequestInterface $request, UserInterface $user = null, bool $remember = false): ServerRequestInterface
    {
        if ($user) return $request->withAttribute(self::user, $user);

        $cookies = Cookies::fromRequest($request);
        if ($cookies->has($this->cookieParams->name) && ($id = $cookies->get($this->cookieParams->name)->getValue()) !== null) {
            if ($remember) $request = $request->withAttribute(self::rememberMe, $remember);
            if ($this->userProvider instanceof Sessionable) {
                if (($user = $this->userProvider->retrieveBySessionId($id)) === null) {
                    throw new AuthenticationException("Session with ID [$id] not found!", $request);
                }

                return $request->withAttribute(self::user, $user);
            }

            if (($user = $this->userProvider->retriveById($id)) === null) {
                throw new AuthenticationException("User with ID [$id] not found!", $request);
            }

            return $request->withAttribute(self::user, $user);
        }

        return $request;
    }

    /**
     * @param ServerRequestInterface $serverRequest
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function unauthorized(ServerRequestInterface $serverRequest, ResponseInterface $response): ResponseInterface
    {
        return $response->withStatus(401);
    }

    /**
     * @param ServerRequestInterface $serverRequest
     * @param ResponseInterface $response
     * @param int|\DateTimeInterface|null $expires
     * @return ResponseInterface
     */
    public function writeData(ServerRequestInterface $serverRequest, ResponseInterface $response): ResponseInterface
    {
        $user = $serverRequest->getAttribute(self::user);

        if ($user instanceof UserInterface) {
            if (!Cookies::fromRequest($serverRequest)->has($this->cookieParams->name)) {
                $setCookie = $this->cookieParams->setCookie($user->getId());
                $remember = $serverRequest->getAttribute(self::rememberMe, false);
                $response = FigResponseCookies::set($response, $remember ?
                    $setCookie->rememberForever() : $setCookie->withExpires(0)
                );
            }

            return $response;
        }

        return $this->clearAuthenticationData($serverRequest, $response);
    }

    /**
     * @param ServerRequestInterface $serverRequest
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function clearData(ServerRequestInterface $serverRequest, ResponseInterface $response): ResponseInterface
    {
        return FigResponseCookies::set($response, $this->cookieParams->setCookie('')->expire());
    }

    public static function getUser(ServerRequestInterface $serverRequest):? UserInterface
    {
        return $serverRequest->getAttribute(self::user);
    }

    /**
     * @param ServerRequestInterface $serverRequest
     * @return bool
     */
    public function isAuthenticated(ServerRequestInterface $serverRequest): bool
    {
        return $serverRequest->getAttribute(self::user) instanceof UserInterface ;
    }
}
