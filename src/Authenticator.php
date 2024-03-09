<?php

namespace Bermuda\Authenticator;

use Bermuda\Authenticator\Events\EventListener;
use Bermuda\Authenticator\Events\EventType;
use Bermuda\Authenticator\Events\FailureAuthenticationEvent;
use Bermuda\Authenticator\Events\LoginAttemptionEvent;
use Bermuda\Authenticator\User\UserInterface;
use Bermuda\Eventor\EventDispatcher;
use Bermuda\Eventor\EventDispatcherInterface;
use Bermuda\Authenticator\Strategy\InteractiveAuthenticationStrategyInterface;
use Bermuda\Eventor\ListenerProviderInterface;
use Bermuda\Eventor\Provider\Provider;
use Bermuda\HTTP\Responder;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * @mixin AuthenticationProvider
 */
final class Authenticator implements MiddlewareInterface
{
    private ?UserInterface $user = null;
    private ListenerProviderInterface $listenerProvider;

    public function __construct(
        public readonly AuthenticationProvider $provider,
        public readonly Responder $responder,
        private readonly InteractiveAuthenticationStrategyInterface $strategy,
        private EventDispatcherInterface $dispatcher = new EventDispatcher
    ) {
        $this->dispatcher = $this->dispatcher->attach($this->listenerProvider = new Provider);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public static function createFromContainer(ContainerInterface $container): self
    {
        return new self($container->get(AuthenticationProvider::class),
            $container->get(Responder::class),
            $container->get(InteractiveAuthenticationStrategyInterface::class)
        );
    }

    public function logout(ServerRequestInterface $serverRequest): ResponseInterface
    {
        return $this->provider->clearData($serverRequest, $this->responder->respond(204));
    }

    /**
     * @param ServerRequestInterface $serverRequest
     * @throws AuthenticationException
     * @return ServerRequestInterface|ResponseInterface
     */
    public function attempt(ServerRequestInterface $serverRequest, bool $fireEvents = false): ServerRequestInterface|ResponseInterface
    {
        if ($fireEvents) {
            $serverRequest = $this->dispatch(new LoginAttemptionEvent($serverRequest))
                ->serverRequest;
            
            try {
                return $this->strategy->attempt($serverRequest, $this->provider);
            }
            catch (AuthenticationException $e) {
                $this->dispatch(FailureAuthenticationEvent::fromException($e, $e->writeResponse($this->responder->respond(401))));
                throw $e;
            }
        }

        return $this->strategy->attempt($serverRequest, $this->provider);
    }

    /**
     * @throws AuthenticationException
     */
    public function authentication(ServerRequestInterface $request, UserInterface $user=null): ServerRequestInterface
    {
        $request = $this->provider->authentication($request, $user);
        $this->user = $this->provider::getUser($request);

        return $request;
    }

    public function listen(EventType $type, EventListener $listener): self
    {
        $this->listenerProvider->listen($type->getType(),
            static function ($event) use ($listener) {
                $listener->handle($event);
                return $event;
        });

        return $this;
    }

    public function dispatch(FailureAuthenticationEvent|LoginAttemptionEvent $event): FailureAuthenticationEvent|LoginAttemptionEvent
    {
        return $this->dispatcher->dispatch($event);
    }

    public function getUser():? UserInterface
    {
        return $this->user;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            $request = $this->authentication($request);
        } catch (AuthenticationException $e) {
            $response = $this->responder->respond(401);
            $response = $e->writeResponse($response);

            $event = FailureAuthenticationEvent::fromException($e, $response);
            $this->dispatcher->dispatch($event);

            return $event->response;
        }

        return $handler->handle($request);
    }
}
