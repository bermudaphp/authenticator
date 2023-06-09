<?php

namespace Bermuda\Authenticator;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AuthenticationException extends \Exception
{
    public const exceptionAttribute = AuthenticationException::class;
    
    public function __construct(string $msg, public readonly ServerRequestInterface $serverRequest, \Throwable $prev = null)
    {
        parent::__construct($msg, 401, $prev);     
    }
    
    /**
     * @param ServerRequestInterface $serverRequest
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public static function writeResponse(ServerRequestInterface $serverRequest, ResponseInterface $response): ResponseInterface
    {
        $response->getBody()->write(json_encode([
            'error_msg' => $e->getMessage(),
            'error_code' => $e->errorCode
        ]));

        return $response;
    }
}
