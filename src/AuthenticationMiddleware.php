<?php

namespace Bermuda\Authenticator;

use Bermuda\HTTP\Responder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class AuthenticationMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly Authenticator $authenticator,
        private readonly Responder $responder
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            $this->authenticator->authentication($request);
        } catch (AuthenticationException $e) {
            $response = $e->writeResponse($this->responder->respond(401));
            return $this->authenticator->clear($response);
        }

        $response = $this->authenticator->getUser() !== null ?
            $handler->handle($request->withAttribute(AuthenticationProvider::userAttribute,
                $this->authenticator->getUser())
            ) : $handler->handle($request);

        return $this->authenticator->write($request, $response);
    }
}
