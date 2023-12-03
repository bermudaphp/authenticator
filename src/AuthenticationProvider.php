<?php

namespace Bermuda\Authenticator;

use Bermuda\Authenticator\Client\ClientInterface;
use Bermuda\Authenticator\User\UserInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface AuthenticationProvider
{
    public const user = UserInterface::class;
    public const rememberMe = 'Bermuda\Authenticator\AuthenticationProvider::rememberMe';

    /**
     * @throws AuthenticationException
     */
    public function authentication(ServerRequestInterface $request, UserInterface $user = null, bool $remember = false): ServerRequestInterface ;
    public function unauthorized(ServerRequestInterface $serverRequest, ResponseInterface $response): ResponseInterface ;

    public function writeData(ServerRequestInterface $serverRequest, ResponseInterface $response): ResponseInterface ;
    public function clearData(ServerRequestInterface $serverRequest, ResponseInterface $response): ResponseInterface ;

    /**
     * @param ServerRequestInterface $serverRequest
     * @return bool
     */
    public function isAuthenticated(ServerRequestInterface $serverRequest): bool ;

    /**
     * @param ServerRequestInterface $serverRequest
     * @return ClientInterface|null
     */
    public static function getUser(ServerRequestInterface $serverRequest):? UserInterface ;
}
