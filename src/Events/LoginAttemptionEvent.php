<?php

namespace Bermuda\Authenticator\Events;

use Psr\Http\Message\ServerRequestInterface;

class LoginAttemptionEvent
{
    public function __construct(
        public readonly ServerRequestInterface $serverRequest
    ) {
    }
}