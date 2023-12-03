<?php

namespace Bermuda\Authenticator\User;

interface UserInterface
{
    public function getId(): string ;

    /**
     * @return string|string[]
     */
    public function getIdentity(): array|string ;

    /**
     * @return string
     */
    public function getCredentials(): string ;
}
