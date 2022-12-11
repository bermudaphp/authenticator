<?php

namespace App\Auth;

use Bermuda\Authentication\UserInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface AuthenticationProvider
{
    public const user_attribute = UserInterface::class;
    public const client_attribute = ClientInterface::class;

    /**
     * @param ServerRequestInterface $request
     * @return ServerRequestInterface
     * @throws AuthenticationException
     */
    public function authentication(ServerRequestInterface $request): ServerRequestInterface ;
    public function authenticateUser(UserInterface $user, ServerRequestInterface $serverRequest): ServerRequestInterface ;
    public function unauthorized(ServerRequestInterface $serverRequest, ResponseInterface $response): ResponseInterface ;
    public function write(ServerRequestInterface $serverRequest, ResponseInterface $response, int|\DateTimeInterface $expires = null): ResponseInterface ;
    public function clear(ServerRequestInterface $serverRequest, ResponseInterface $response): ResponseInterface ;

    /**
     * @param ServerRequestInterface $serverRequest
     * @return bool
     */
    public function isAuthenticated(ServerRequestInterface $serverRequest): bool ;

    /**
     * @param ServerRequestInterface $serverRequest
     * @return ClientInterface|null
     */
    public static function getClient(ServerRequestInterface $serverRequest):? ClientInterface ;

    /**
     * @param ServerRequestInterface $serverRequest
     * @return ClientInterface|null
     */
    public static function getUser(ServerRequestInterface $serverRequest):? UserInterface ;
}