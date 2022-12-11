<?php

namespace App\Auth;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AuthenticationException extends \Exception
{
    public const exception_attribute = AuthenticationException::class;
    public readonly int $errorCode;
    public readonly ServerRequestInterface $serverRequest;
    public static function create(string $message, int $errorCode, ServerRequestInterface $serverRequest, \Throwable $prev = null): self
    {
        $self = new static($message, 401, $prev);

        $self->errorCode = $errorCode;
        $self->serverRequest = $serverRequest;

        return $self;
    }

    /**
     * @param ServerRequestInterface $serverRequest
     * @return static|null
     */
    public static function getFromServerRequest(ServerRequestInterface $serverRequest): ?self
    {
        return $serverRequest->getAttribute(static::exception_attribute);
    }

    /**
     * @param ServerRequestInterface $serverRequest
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public static function writeResponse(ServerRequestInterface $serverRequest, ResponseInterface $response): ResponseInterface
    {
        $e = static::getFromServerRequest($serverRequest);
        if ($e instanceof AuthenticationException) {
            $response->getBody()->write(json_encode([
                'error_msg' => $e->getMessage(),
                'error_code' => $e->errorCode
            ]));

            return $response;
        }

        $response->getBody()->write(json_encode([
            'error_msg' => 'Access denied. Unauthorized request',
        ]));

        return $response;
    }
}