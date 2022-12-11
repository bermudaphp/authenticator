<?php

namespace App\Auth\Events;

use Psr\Http\Message\ServerRequestInterface;

class BeforeAuthenticationEvent extends AuthEvent
{
    public function __construct(ServerRequestInterface $request)
    {
        parent::__construct($request);
    }
}