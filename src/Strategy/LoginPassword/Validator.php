<?php

namespace Bermuda\Authenticator\Strategy\LoginPassword;

use Bermuda\Validation\Rules\Required;

final class Validator extends \Bermuda\Validation\Validator implements ValidatorInterface
{
    public function __construct(string $identityField, string $credentialsFiled)
    {
        parent::__construct([
            $identityField => new Required($identityField),
            $credentialsFiled => new Required($credentialsFiled)
        ]);
    }
}