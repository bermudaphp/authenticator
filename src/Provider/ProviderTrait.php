<?php

namespace App\auth\provider;

use App\Auth\AuthenticationProvider;
use App\Auth\ClientInterface;
use Bermuda\Authentication\UserInterface;
use Psr\Http\Message\ServerRequestInterface;

trait ProviderTrait
{
    /**
     * @param ServerRequestInterface $serverRequest
     * @return ClientInterface|null
     */
    public static function getClient(ServerRequestInterface $serverRequest): ?ClientInterface
    {
        return $serverRequest->getAttribute(AuthenticationProvider::client_attribute);
    }

    /**
     * @param UserInterface $user
     * @param ServerRequestInterface $serverRequest
     * @return ServerRequestInterface
     */
    public function authenticateUser(UserInterface $user, ServerRequestInterface $serverRequest): ServerRequestInterface
    {
        return $serverRequest->withAttribute(AuthenticationProvider::user_attribute, $user);
    }

    /**
     * @param ServerRequestInterface $serverRequest
     * @return UserInterface|null
     */
    public static function getUser(ServerRequestInterface $serverRequest): ?UserInterface
    {
        return $serverRequest->getAttribute(AuthenticationProvider::user_attribute);
    }
}