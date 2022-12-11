<?php

namespace App\Auth;

use App\Auth\Events\AfterAuthenticationEvent;
use App\Auth\Events\AuthEvent;
use App\Auth\Events\AuthEventListenerInterface;
use App\Auth\Events\BeforeAuthenticationEvent;
use App\Auth\Events\EventType;
use App\Auth\Events\FailureAuthenticationEvent;
use Bermuda\Authentication\UserInterface;
use Bermuda\Config\Config;
use Bermuda\Eventor\Event;
use Bermuda\Eventor\EventDispatcher;
use Bermuda\Eventor\EventDispatcherInterface;
use Bermuda\Eventor\ListenerProviderInterface;
use Bermuda\Eventor\Provider\Provider;
use Bermuda\HTTP\Responder;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class Authenticator implements MiddlewareInterface
{
    /**
     * @var AuthenticationProvider[]
     */
    private array $providers = [];
    private ListenerProviderInterface $listenerProvider;

    public function __construct(
        AuthenticationProvider $provider,
        private readonly Responder $responder,
        private EventDispatcherInterface $dispatcher = new EventDispatcher
    ) {
        $this->addProvider($provider);
        $this->dispatcher = $this->dispatcher->attach($this->listenerProvider = new Provider);
    }

    /**
     * @param AuthenticationProvider $provider
     * @return $this
     */
    public function addProvider(AuthenticationProvider $provider): self
    {
        $this->providers[$provider::class] = $provider;
        return $this;
    }

    /**
     * @param EventType $eventType
     * @param AuthEventListenerInterface $listener
     * @return $this
     */
    public function listen(EventType $eventType, AuthEventListenerInterface $listener): self
    {
        $this->listenerProvider->listen($eventType->getEventType(), static function(AuthEvent $event) use ($listener): Event {
            $listener->handle($event);
            return $event;
        });

        return $this;
    }

    /**
     * @param string|AuthenticationProvider $provider
     * @return bool
     */
    public function hasProvider(string|AuthenticationProvider $provider): bool
    {
        return array_key_exists(is_string($provider)
            ? $provider : $provider::class , $this->providers);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public static function createFromContainer(ContainerInterface $container): self
    {
        $config = $container->get(Config::CONTAINER_CONFIG_KEY);
        $providers = $config['auth']['providers'];
        $provider = array_shift($providers);
        $authenticator = new self($container->get($provider), $container->get(Responder::class));
        foreach ($providers as $provider) $authenticator->addProvider($container->get($provider));
        return $authenticator;
    }

    /**
     * @param UserInterface $user
     * @param ServerRequestInterface $request
     * @return ServerRequestInterface
     */
    public function authenticateUser(UserInterface $user, ServerRequestInterface $request): ServerRequestInterface
    {
        return $request->withAttribute(AuthenticationProvider::user_attribute, $user);
    }

    /**
     * @param ServerRequestInterface $request
     * @return ServerRequestInterface|ResponseInterface
     */
    public function authentication(ServerRequestInterface $request): ServerRequestInterface|ResponseInterface
    {
        foreach ($this->providers as $provider) {
            try {
                $request = $this->dispatcher->dispatch(new BeforeAuthenticationEvent($request))->request;
                $request = $provider->authentication($request);
            } catch (AuthenticationException $e) {
                $response = $provider->unauthorized($e->serverRequest, $this->responder->respond(401));
                return $this->dispatcher->dispatch(new FailureAuthenticationEvent($request, $response, $e))->response;
            }
        }

        if (!$this->isAuthenticated($request)) {
            return $this->dispatcher->dispatch(
                new AfterAuthenticationEvent($request, false, $this->unauthorized($request, $this->responder->respond(401)))
            )->response;
        }

        return $this->dispatcher->dispatch(new AfterAuthenticationEvent($request, true))->request;
    }

    /**
     * @param ServerRequestInterface $serverRequest
     * @return bool
     */
    public function isAuthenticated(ServerRequestInterface $serverRequest): bool
    {
        foreach ($this->providers as $provider) if ($provider->isAuthenticated($serverRequest)) return true;
        return false;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function unauthorized(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        foreach ($this->providers as $provider) $response = $provider->unauthorized($request, $this->responder->respond(401));
        return $response;
    }

    /**
     * @param ServerRequestInterface $serverRequest
     * @param ResponseInterface $response
     * @param \DateTimeInterface|null $expires
     * @return ResponseInterface
     */
    public function write(ServerRequestInterface $serverRequest, ResponseInterface $response, \DateTimeInterface $expires = null): ResponseInterface
    {
        foreach ($this->providers as $provider) $response = $provider->write($serverRequest, $response, $expires);
        return $response;
    }

    /**
     * @param ServerRequestInterface $serverRequest
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function clear(ServerRequestInterface $serverRequest, ResponseInterface $response): ResponseInterface
    {
        foreach ($this->providers as $provider) $response = $provider->clear($serverRequest, $response);
        return $response;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return ($result = $this->authentication($request)) instanceof ResponseInterface ? $result : $handler->handle($result);
    }
}