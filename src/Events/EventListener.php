<?php

namespace Bermuda\Authenticator\Events;

interface EventListener
{
    public function handle(FailureAuthenticationEvent|LoginAttemptionEvent $event): void ;
}
