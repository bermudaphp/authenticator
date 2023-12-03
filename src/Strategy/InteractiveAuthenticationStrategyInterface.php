<?php

namespace Bermuda\Authenticator\Strategy;

use Bermuda\Authenticator\AuthenticationException;
use Bermuda\Authenticator\AuthenticationProvider;
use Bermuda\Authenticator\Handler\Query;
use Bermuda\Authenticator\Handler\RequestParserInterface;
use Bermuda\Authenticator\User\UserInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface InteractiveAuthenticationStrategyInterface
{
    /**
     * @throws AuthenticationException
     */
    public function attempt(ServerRequestInterface $request, AuthenticationProvider $provider): ServerRequestInterface|ResponseInterface ;
}
