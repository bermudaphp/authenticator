<?php

namespace App\Auth\Provider\Token;

class TokenException extends \Exception
{
    public const INVALID_TOKEN = 1;
    public const TOKEN_EXPIRED = 2;

    public static function tokenExpired(): self
    {
        return new self('Token expired', self::TOKEN_EXPIRED);
    }

    public static function invalidToken(): self
    {
        return new self('Token is invalid', self::INVALID_TOKEN);
    }
}