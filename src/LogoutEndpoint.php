<?php

namespace App\Auth;

use Bermuda\HTTP\Responder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class LogoutEndpoint implements RequestHandlerInterface
{
    public function __construct(
        private readonly Responder $responder,
        private readonly Authenticator $authenticator
    ) {
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->authenticator->unauthorized($this->responder->respond(204));
    }
}