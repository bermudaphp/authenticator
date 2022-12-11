<?php

namespace Bermuda\Authenticator\Provider;

use Bermuda\Authenticator\AuthenticationException;
use Bermuda\Authenticator\AuthenticationProvider;
use Bermuda\Authenticator\UserInterface;
use Bermuda\Authenticator\UserProviderInterface;
use Bermuda\HTTP\Cookie\CookieParams;
use Dflydev\FigCookies\Cookies;
use Dflydev\FigCookies\FigResponseCookies;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class CookieProvider implements AuthenticationProvider
{
    use ProviderTrait;
    public function __construct(
        private readonly UserProviderInterface $userProvider,
        private readonly CookieParams $cookieParams,
        private readonly string $headerName = 'x-user-id'
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
     * @param ServerRequestInterface $request
     * @return ServerRequestInterface
     * @throws AuthenticationException
     */
    public function authentication(ServerRequestInterface $request): ServerRequestInterface
    {
        $cookies = Cookies::fromRequest($request);
        if ($cookies->has($this->cookieParams->name) && ($userId = $cookies->get($this->cookieParams->name)->getValue()) !== null) {
            if (($user = $this->userProvider->provide($userId)) === null) {
                throw AuthenticationException::create(
                    "User with ID [$userId] not found!",
                    1, $request
                );
            }

            return $request->withAttribute(self::user_attribute, $user);
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
        return AuthenticationException::writeResponse($serverRequest, $response);
    }

    /**
     * @param ServerRequestInterface $serverRequest
     * @param ResponseInterface $response
     * @param int|\DateTimeInterface|null $expires
     * @return ResponseInterface
     */
    public function write(ServerRequestInterface $serverRequest, ResponseInterface $response, int|\DateTimeInterface $expires = null): ResponseInterface
    {
        $user = $serverRequest->getAttribute(self::user_attribute);

        if ($user instanceof UserInterface) {
            if (!Cookies::fromRequest($serverRequest)->has($this->cookieParams->name)) {
                $setCookie = $this->cookieParams->setCookie($user->getId());
                $response = FigResponseCookies::set($response, $expires ?
                    $setCookie->withExpires($expires) : $setCookie->rememberForever()
                )->withHeader($this->headerName, $user->getId());
            }

            return $response;
        }

        return $this->clear($serverRequest, $response);
    }

    /**
     * @param ServerRequestInterface $serverRequest
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function clear(ServerRequestInterface $serverRequest, ResponseInterface $response): ResponseInterface
    {
        return FigResponseCookies::set($response, $this->cookieParams->setCookie('')->expire());
    }

    /**
     * @param UserInterface $user
     * @param ServerRequestInterface $serverRequest
     * @return ServerRequestInterface
     */
    public function authenticateUser(UserInterface $user, ServerRequestInterface $serverRequest): ServerRequestInterface
    {
        return $serverRequest->withAttribute(self::user_attribute, $user);
    }

    /**
     * @param ServerRequestInterface $serverRequest
     * @return bool
     */
    public function isAuthenticated(ServerRequestInterface $serverRequest): bool
    {
        return $serverRequest->getAttribute(self::user_attribute) instanceof UserInterface ;
    }
}
