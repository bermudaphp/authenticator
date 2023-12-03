<?php

namespace Bermuda\Authenticator\Events;

enum EventType
{
    case attempt;
    case failure;

    public function getType(): string
    {
        return $this->name === 'attempt' ? LoginAttemptionEvent::class : FailureAuthenticationEvent::class;
    }
}
