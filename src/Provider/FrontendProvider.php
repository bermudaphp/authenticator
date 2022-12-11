<?php

namespace App\Auth\Provider;

use Bermuda\HTTP\Cookie\CookieParams;
use Bermuda\Authenticator\AuthenticationException;
use Bermuda\Authenticator\AuthenticationProvider;
use Bermuda\Authenticator\Client\JsClient;
use Bermuda\Authenticator\ClientInterface;
use Bermuda\Authenticator\Provider\Token\Token;
use Bermuda\Authenticator\Provider\Token\TokenEncoderInterface;
use Bermuda\Authenticator\Provider\Token\TokenException;
use Bermuda\Authenticator\UserInterface;
use Dflydev\FigCookies\Cookies;
use Dflydev\FigCookies\FigResponseCookies;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class FrontendProvider implements AuthenticationProvider
{
    use ProviderTrait;
    public const token_attribute = Token::class;
    public function __construct(
        private readonly string $clientID,
        private readonly CookieParams $cookieParams,
        private readonly TokenEncoderInterface $encoder,
        private readonly ?int $tokenLifeTime = null
    ) {
    }

    private const payload_clientID = 'clientID';

    /**
     * @param ContainerInterface $container
     * @return static
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public static function fromContainer(ContainerInterface $container): self
    {
        $config = $container->get('config')['auth'];
        return new self($config['secret'],
            CookieParams::createFromArray(
                $config['cookie'][FrontendProvider::class]
            ),
            $container->get(TokenEncoderInterface::class)
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
        if ($cookies->has($this->cookieParams->name) && ($token = $cookies->get($this->cookieParams->name)->getValue()) !== null) {
            try {
                $token = $this->encoder->decode($token);
                return $request->withAttribute(self::token_attribute, $token)
                    ->withAttribute(self::client_attribute, new JsClient($token->payload[ClientInterface::class]));
            } catch (TokenException $prev) {
                throw AuthenticationException::create(
                    "Access denied: {$prev->getMessage()}",
                    401, $request, $prev
                );
            }
        }

        return $request;
    }

    public function authenticateUser(UserInterface $user, ServerRequestInterface $serverRequest): ServerRequestInterface
    {
        return $serverRequest;
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

    public function write(ServerRequestInterface $serverRequest, ResponseInterface $response, null|int|\DateTimeInterface $expires = 60*60): ResponseInterface
    {
        if (!Cookies::fromRequest($serverRequest)->has($this->cookieParams->name)) {
            $token = new Token($this->payload(), $this->encoder);
            $cookie = $this->cookieParams->setCookie($token)
                ->withExpires($expires);
            $response = FigResponseCookies::set($response, $cookie);
        }

        return $response;
    }

    /**
     * @param ServerRequestInterface $serverRequest
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function clear(ServerRequestInterface $serverRequest, ResponseInterface $response): ResponseInterface
    {
        return $response;
    }

    private function payload(): array
    {
        return [
            self::client_attribute => $this->clientID,
            Token::issuer_attribute => $this->cookieParams->domain,
            Token::lifetime_attribute => $this->tokenLifeTime
        ];
    }

    /**
     * @param ServerRequestInterface $serverRequest
     * @return bool
     */
    public function isAuthenticated(ServerRequestInterface $serverRequest): bool
    {
        return $serverRequest->getAttribute(self::token_attribute) instanceof Token ;
    }
}
