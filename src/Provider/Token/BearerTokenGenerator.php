<?php

namespace App\auth\provider\Token;

class BearerTokenGenerator implements BearerTokenGeneratorInterface
{
    public function __construct(private readonly int $tokenLength = 16) {
    }

    /**
     * @return string
     */
    private function getTokenPattern(): string
    {
        return '~(Bearer: ?)([a-zA-Z0-9]{'. $this->tokenLength * 2 .'})$~';
    }

    /**
     * @param array|null $data
     * @return string
     * @throws \Exception
     */
    public function generate(array $data = null): string
    {
        return bin2hex(random_bytes($this->tokenLength));
    }

    /**
     * @param string $header
     * @return string|null
     */
    public function parseHeader(string $header):? string
    {
        if (preg_match($this->getTokenPattern(), $header, $matches) === 1) {
            return $matches[2];
        }

        return null;
    }
}