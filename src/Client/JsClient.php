<?php

namespace Bermuda\Authenticator\Client;

use Bermuda\Authenticator\ClientInterface;

final class JsClient implements ClientInterface
{
    public function __construct(public readonly string $id) {
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return null
     */
    public function getSecret(): ?string
    {
        return null;
    }
}
