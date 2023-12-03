<?php

namespace Bermuda\Authenticator\User;

use Bermuda\Hasher\HasherInterface;

final class PasswordHashCredentialValidator implements CredentialValidatorInterface
{
    public function __construct(
        private readonly HasherInterface $hasher
    ) {
    }

    /**
     * @param string $input
     * @param string $credentials
     * @return bool
     */
    public function validateCredentials(string $input, string $credentials): bool
    {
        return $this->hasher->validateHash($input, $credentials);
    }
}