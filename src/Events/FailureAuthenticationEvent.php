<?php

namespace App\Auth\Events;

use App\Auth\AuthenticationException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class FailureAuthenticationEvent extends AuthEvent
{
    public function __construct(
        ServerRequestInterface $request,
        public ResponseInterface $response,
        public AuthenticationException $authenticationException
    ) {
        parent::__construct($request);
    }
}