<?php

namespace Bermuda\Authenticator;

use Bermuda\HTTP\Responder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class AuthenticatorMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly Authenticator $authenticator,
        private readonly Responder $responder) {
    }

    /**
     * @throws AuthenticationException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            $this->authenticator->authentication($request);
        } catch (AuthenticationException $e) {
            $response = $this->responder->respond(401, [
                'error_msg' => $e->getMessage(),
                'error_code' => $e->getPrevious() ? $e->getCode() : 401
            ]);

            return $this->authenticator->clearAuthInfo($response);
        }

        $response = $this->authenticator->getUser() !== null ?
            $handler->handle($request->withAttribute(AuthenticationProvider::user_attribute,
                $this->authenticator->getUser())
            ) : $handler->handle($request);

        return $this->authenticator->write($request, $response);
    }
}
