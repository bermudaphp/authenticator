<?php

namespace Bermuda\Authenticator\Client;

use Bermuda\Authenticator\ClientInterface;

class Client implements ClientInterface
{
    public function __construct(
        private readonly string $id,
        private readonly ?string $secret = null
    ) {
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getSecret(): ?string
    {
        return $this->secret;
    }
}
