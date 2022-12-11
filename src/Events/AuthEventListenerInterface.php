<?php

namespace App\Auth\Events;

interface AuthEventListenerInterface
{
    public function handle(AuthEvent $event): void ;
}