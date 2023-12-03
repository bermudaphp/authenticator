<?php

namespace Bermuda\Authenticator\Provider\Sessions;

use Bermuda\Authenticator\User\UserInterface;

interface Sessionable
{
    public function retrieveBySessionId(string $id):? UserInterface ;
}
