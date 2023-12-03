<?php

namespace Bermuda\Authenticator\Strategy\LoginPassword;

use Bermuda\HTTP\Exception\BadRequestException;
use Bermuda\Validation\ValidationException;
use Psr\Http\Message\ServerRequestInterface;

interface RequestParserInterface
{
    /**
     * @throws ValidationException
     */
    public function parseRequest(ServerRequestInterface $request): Query ;
}