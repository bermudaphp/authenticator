<?php

namespace Bermuda\Authenticator\Provider\Token;

use Psr\Container\ContainerInterface;

final class TokenEncoder implements TokenEncoderInterface
{
    public function __construct(
        private readonly string $secret,
    ) {
    }

    /**
     * @param ContainerInterface $container
     * @return static
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public static function fromContainer(ContainerInterface $container): self
    {
        return new self($container->get('config')['auth']['secret']);
    }

    /**
     * @param Token $token
     * @return string
     */
    public function encode(Token $token): string
    {
        $payload = $token->payload;
        $payload['iat'] = strtotime('now');

        $encodedHeader = base64_encode(json_encode([
            'typ' => $token->type, 'alg' => $token->algo
        ]));
        $encodedPayload = base64_encode(json_encode($payload));

        return "$encodedHeader.$encodedPayload." . $this->sign($token->algo, $encodedHeader, $encodedPayload);
    }

    /**
     * @throws TokenException
     */
    public function decode(string $token): Token
    {
        list($encodedHeader, $encodedPayload) = explode('.', $token, 3);

        $header = json_decode(base64_decode($encodedHeader), true);
        $payload = json_decode(base64_decode($encodedPayload), true);

        if ($encodedHeader . '.' . $encodedPayload . '.' . $this->sign($header['alg'], $encodedHeader, $encodedPayload) !== $token) {
            throw TokenException::invalidToken();
        }

        if (isset($payload['exp']) && strtotime('now') > $payload['exp']) {
            throw TokenException::tokenExpired();
        }

        return new Token($payload, $this, $header['typ'], $header['alg']);
    }

    private function sign(string $algo, string $encodedHeader, string $encodedPayload): string
    {
        return base64_encode(
            hash_hmac($algo, "$encodedHeader.$encodedPayload", $this->secret, true)
        );
    }

}
