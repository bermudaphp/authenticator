<?php

namespace Bermuda\Authenticator\Strategy\LoginPassword;

use Bermuda\Validation\ValidationException;
use Psr\Http\Message\ServerRequestInterface;
use function Bermuda\Stdlib\to_array;

final class RequestParser implements RequestParserInterface
{
    public function __construct(
        public readonly string $identityField,
        public readonly string $credentialsField,
        private ?ValidatorInterface $validator = null,
        public readonly string $rememberMeField = 'remember',
        public readonly string $languageField = 'language',
    ) {
        if ($this->validator === null) {
            $this->validator = new Validator($this->identityField, $this->credentialsField);
        }
    }

    /**
     * @param ServerRequestInterface $request
     * @return Query
     * @throws ValidationException
     */
    public function parseRequest(ServerRequestInterface $request): Query
    {
        $data = to_array($request->getParsedBody());
        $this->validator->validate($data);

        return new Query(
            $data[$this->identityField],
            $data[$this->credentialsField],
            isset($data[$this->rememberMeField]),
                $data[$this->languageField] ?? 'en_US'
        );
    }
}