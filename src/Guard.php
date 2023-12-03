<?php

namespace Bermuda\Authenticator;

use Bermuda\HTTP\Responder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class Guard implements MiddlewareInterface
{
    public function __construct(
        private readonly Responder $responder,
        private readonly AuthenticationProvider $provider
    ){
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!$this->provider->isAuthenticated($request)) {
            return $this->provider->unauthorized($this->responder->respond(401));
        }

        return $handler->handle($request);
    }
}