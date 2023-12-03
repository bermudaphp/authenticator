<?php

namespace Bermuda\Authenticator\Handler;

use Bermuda\Authenticator\Authenticator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class LogoutHandler implements RequestHandlerInterface
{
    public function __construct(
        private readonly Authenticator $authenticator
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->authenticator->logout($request);
    }
}