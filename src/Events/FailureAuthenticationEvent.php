<?php

namespace Bermuda\Authenticator\Events;

use Bermuda\Authenticator\AuthenticationException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class FailureAuthenticationEvent
{
    public function __construct(
        public readonly ServerRequestInterface $serverRequest,
        public ResponseInterface $response,
        public readonly AuthenticationException $exception
    ) {
    }

    public static function fromException(AuthenticationException $exception, ResponseInterface $response): self
    {
        return new self(
            $exception->serverRequest,
            $exception->writeResponse($exception->serverRequest, $response),
            $exception
        );
    }
}
