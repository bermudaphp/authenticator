<?php

namespace App\Auth\Events;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AfterAuthenticationEvent extends AuthEvent
{
    public function __construct(
        ServerRequestInterface $request,
        public bool $isAuthenticated,
        public ?ResponseInterface $response = null
    ) {
        parent::__construct($request);
    }
}