<?php

namespace Bermuda\Authenticator\Provider\Token;

interface BearerTokenGeneratorInterface
{
    public function generate(array $data = null): string ;
    public function parseHeader(string $header):? string ;
}
