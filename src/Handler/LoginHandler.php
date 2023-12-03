<?php

namespace Bermuda\Authenticator\Handler;

use Bermuda\Authenticator\AuthenticationException;
use Bermuda\Authenticator\AuthenticationProvider;
use Bermuda\Authenticator\Authenticator;
use Bermuda\Authenticator\Events\FailureAuthenticationEvent;
use Bermuda\Authenticator\User\CredentialValidatorInterface;
use Bermuda\Authenticator\User\UserProviderInterface;
use Bermuda\Eventor\EventDispatcher;
use Bermuda\Hasher\Hash;
use Bermuda\HTTP\Responder;
use Bermuda\Validation\ValidationException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Translator\Translator;
use Translator\TranslatorInterface;
use Translator\TranslatorProviderInterface;

final class LoginHandler implements RequestHandlerInterface
{
    public const translatorToken = LoginHandler::class;

    public function __construct(
        private readonly Authenticator $authenticator,
        private readonly bool $fireEvents = false
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $result = $this->authenticator->attempt($request, $this->fireEvents);
        } catch (AuthenticationException $exception) {
            if (!$this->fireEvents) {
                return $exception->writeResponse($request,
                    $this->authenticator->responder->respond(401)
                );
            }

            $event = FailureAuthenticationEvent::fromException($exception,
                $this->authenticator->responder->respond(401));
            $event = $this->authenticator->dispatch($event);

            return $event->response;
        }

        if ($result instanceof ResponseInterface) return $result;
        return $this->authenticator->provider->writeData($result,
            $this->authenticator->responder->respond(200)
        );
    }
}