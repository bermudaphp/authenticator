<?php

namespace Bermuda\Authenticator\Events;

interface AuthEventListenerInterface
{
    public function handle(AuthEvent $event): void ;
}
