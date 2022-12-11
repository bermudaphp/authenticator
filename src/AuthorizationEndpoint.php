<?php

namespace App\Auth;

use App\Entity\EntityNotFound;
use App\Repository\Contract\UserRepository;
use Bermuda\Clock\Clock;
use Bermuda\HTTP\Responder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use function App\_post;

final class AuthorizationEndpoint implements RequestHandlerInterface
{
    public function __construct(
        private readonly UserRepository $repository,
        private readonly Authenticator $authenticator,
        private readonly Responder $responder
    ) {
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if (strtolower($request->getMethod()) === 'options') return $this->responder->respond(204);

        $data = $this->getUserIdentityAndUserCredential($request);
        try {
            $user = $this->repository->findByIdentity($data['identity']);
            if ($user->hash->validate($data['credential'])) {
                $request = $this->authenticator->authenticateUser($user, $request);
                return $this->authenticator->write($request, $this->responder->respond(204),
                    ($data['remember'] ?? false) ? Clock::now()->addYears(5) : null
                );
            }
        } catch (EntityNotFound) {
            return $this->responder->respond(401, ['message' => 'Invalid email or password']);
        }

        return $this->responder->respond(401, ['message' => 'Invalid email or password']);
    }

    /**
     * @param ServerRequestInterface $request
     * @return array
     */
    private function getUserIdentityAndUserCredential(ServerRequestInterface $request): array
    {
        return _post($request)->only(['identity', 'credential', 'remember'])->toArray();
    }
}