<?php

namespace App\auth\provider\Token;

interface BearerTokenGeneratorInterface
{
    public function generate(array $data = null): string ;
    public function parseHeader(string $header):? string ;
}