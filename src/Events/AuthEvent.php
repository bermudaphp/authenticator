<?php

namespace Bermuda\Authenticator\Events;

use Bermuda\Eventor\Event;
use Psr\Http\Message\ServerRequestInterface;

class AuthEvent extends Event
{
    public function __construct(public readonly ServerRequestInterface $request) {
    }
}
