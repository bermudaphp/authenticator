<?php

namespace App\auth\provider;

use Bermuda\Arrayable;

final class TokenMap implements Arrayable
{
    public function __construct(private readonly array $map)
    {
    }

    /**
     * @param string $token
     * @return bool
     */
    public function has(string $token): bool
    {
        return in_array($token, $this->map);
    }

    /**
     * @param string $token
     * @return string|null
     */
    public function getClientID(string $token):? string
    {
        $key = array_search($token, $this->map);
        if ($key !== false) {
            return $this->map[$key];
        }

        return null;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return $this->map;
    }
}