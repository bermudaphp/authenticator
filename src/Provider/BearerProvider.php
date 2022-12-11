<?php

namespace Bermuda\Authenticator\Provider;

use Bermuda\Authenticator\Client\Client;
use Bermuda\Authenticator\AuthenticationException;
use Bermuda\Authenticator\AuthenticationProvider;
use Bermuda\Authenticator\Provider\Token\BearerTokenGeneratorInterface;
use Bermuda\Authenticator\Provider\Token\TokenMap;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class BearerProvider implements AuthenticationProvider
{
    use ProviderTrait;
    private const headerName = 'Authorization';
    public function __construct(private readonly TokenMap $map, private BearerTokenGeneratorInterface $generator)
    {
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
        return new self(new TokenMap($config['token-map']), $container->get(BearerTokenGeneratorInterface::class));
    }

    /**
     * @param ServerRequestInterface $request
     * @return ServerRequestInterface
     * @throws AuthenticationException
     */
    public function authentication(ServerRequestInterface $request): ServerRequestInterface
    {
        if (($token = $this->getTokenFromRequest($request)) !== null) {
            if (!$this->map->has($token)) {
                throw AuthenticationException::create('The token is missing from the map', 401, $request);
            }

            return $request->withAttribute(self::client_attribute,
                new Client($this->map->getClientID($token), $token)
            );
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
        return $response;
    }

    /**
     * @param ServerRequestInterface $serverRequest
     * @param ResponseInterface $response
     * @param \DateTimeInterface|int|null $expires
     * @return ResponseInterface
     */
    public function write(ServerRequestInterface $serverRequest, ResponseInterface $response, \DateTimeInterface|int $expires = null): ResponseInterface
    {
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

    /**
     * @param ServerRequestInterface $serverRequest
     * @return bool
     */
    public function isAuthenticated(ServerRequestInterface $serverRequest): bool
    {
        return $serverRequest->getAttribute(self::client_attribute) instanceof Client;
    }

    /**
     * @param ServerRequestInterface $request
     * @return string|null
     * @throws AuthenticationException
     */
    private function getTokenFromRequest(ServerRequestInterface $request):? string
    {
       if ($request->hasHeader(self::headerName)) {
           $token = $this->generator->parseHeader($request->getHeaderLine(self::headerName));
           if ($token === null) {
               throw AuthenticationException::create('Invalid bearer token', 401, $request);
           }

           return $token;
        }

        return null;
    }
}
