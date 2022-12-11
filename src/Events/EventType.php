<?php

namespace Bermuda\Authenticator\Events;

enum EventType
{
    case beforeAuthentication;
    case failureAuthentication;
    case afterAuthentication;

    public function getEventType(): string
    {
        switch ($this->name) {
            case self::beforeAuthentication->name : return BeforeAuthenticationEvent::class;
            case self::afterAuthentication->name : return AfterAuthenticationEvent::class;
            case self::failureAuthentication->name : return FailureAuthenticationEvent::class;
        }
    }
}
