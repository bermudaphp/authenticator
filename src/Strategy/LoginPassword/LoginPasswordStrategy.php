<?php

namespace Bermuda\Authenticator\Strategy\LoginPassword;

use Bermuda\Authenticator\AuthenticationException;
use Bermuda\Authenticator\AuthenticationProvider;
use Bermuda\Authenticator\Authenticator;
use Bermuda\Authenticator\Events\FailureAuthenticationEvent;
use Bermuda\Authenticator\Strategy\InteractiveAuthenticationStrategyInterface;
use Bermuda\Authenticator\User\CredentialValidatorInterface;
use Bermuda\Authenticator\User\UserProviderInterface;
use Bermuda\HTTP\Responder;
use Bermuda\Validation\ValidationException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Translator\Translator;
use Translator\TranslatorProviderInterface;

class LoginPasswordStrategy implements InteractiveAuthenticationStrategyInterface
{
    public const invalidCredentials = 'invalidCredentials';
    public function __construct(
        private readonly TranslatorProviderInterface $translatorProvider,
        private readonly UserProviderInterface $userProvider,
        private readonly CredentialValidatorInterface $credentialValidator,
        private readonly RequestParserInterface $parser,
        private readonly Responder $responder
    ) {
    }

    /**
     * @throws AuthenticationException
     * @return ServerRequestInterface
     */
    public function attempt(ServerRequestInterface $request, AuthenticationProvider $provider): ServerRequestInterface|ResponseInterface
    {
        try {
            $query = $this->parser->parseRequest($request);
        } catch (ValidationException $e) {
            return $this->responder->respond(422, $e->toJson());
        }

        $user = $this->userProvider->retrive($query->userIdentity);
        if (!$user || !$this->credentialValidator->validateCredentials($query->userCredentials, $user->getCredentials())) {
            $translator = $this->translatorProvider->getTranslator($query->language ?? 'en_US');
            $response = $this->responder->respond(401);

            if (!$translator) {
                $translator = new Translator('en_US',
                    [self::invalidCredentials => 'Invalid user identity or credential']
                );
            }

            throw new AuthenticationException(
                $translator->translate(self::invalidCredentials,
                    'Invalid user identity or credential'), $request
            );
        }

        return $provider->authentication($request, $user, $query->rememberMe);
    }
}
