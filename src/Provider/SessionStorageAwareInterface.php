<?php

namespace Bermuda\Authenticator\Provider;

interface SessionStorageAwareInterface
{
    public function getUserSessions(): array ;
    public function getCurrentSession(): object ;
}
