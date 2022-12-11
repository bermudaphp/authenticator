<?php

namespace Bermuda\Authenticator\Provider\Token;

interface TokenEncoderInterface
{
    /**
     * @param Token $token
     * @return string
     */
    public function encode(Token $token): string ;

    /**
     * @param string $token
     * @return Token
     * @throws TokenException
     */
    public function decode(string $token): Token ;
}
