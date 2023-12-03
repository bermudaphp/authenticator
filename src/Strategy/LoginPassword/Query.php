<?php

namespace Bermuda\Authenticator\Strategy\LoginPassword;

final class Query
{
    public function __construct(
        public readonly string|array $userIdentity,
        public readonly string $userCredentials,
        public readonly bool $rememberMe = false,
        public readonly ?string $language = null
    ) {
    }
}