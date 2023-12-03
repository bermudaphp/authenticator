<?php

namespace Bermuda\Authenticator\User;

interface CredentialValidatorInterface
{
    /**
     * @param string $input
     * @param string $credentials
     * @return bool
     */
    public function validateCredentials(string $input, string $credentials): bool ;
}