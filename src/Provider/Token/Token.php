<?php

namespace Bermuda\Authenticator\Provider\Token;

use DateTimeInterface;

final class Token implements \Stringable
{
    public readonly ?string $issuer;
    public readonly ?string $subject;
    public readonly ?string $audience;
    public readonly ?DateTimeInterface $issueTime;
    public readonly ?DateTimeInterface $notBefore;
    public readonly ?DateTimeInterface $expirationTime;

    private ?string $tokenString = null;

    public const issuer_attribute = 'iss';
    public const lifetime_attribute = 'lifetime';
    public const subject_attribute = 'sub';
    public const audience_attribute = 'aud';
    public const not_before_attribute = 'nbf';

    public function __construct(
        public readonly array $payload,
        private readonly TokenEncoderInterface $encoder,
        public readonly string $type = 'jwt',
        public readonly string $algo = 'sha512',
    ) {
        if (isset($this->payload[self::issuer_attribute])) {
            $this->issuer = $this->payload[self::issuer_attribute];
        }

        if (isset($this->payload[self::subject_attribute])) {
            $this->subject = $this->payload[self::subject_attribute];
        }

        if (isset($this->payload[self::audience_attribute])) {
            $this->audience = $this->payload[self::audience_attribute];
        }

        $this->issueTime = new \DateTimeImmutable();

        if (!empty($this->payload[self::lifetime_attribute])) {
            $this->expirationTime = new \DateTimeImmutable();
            $this->expirationTime->setTimestamp(
                $this->issueTime->getTimestamp() + $this->payload[self::lifetime_attribute]
            );
        }

        if (isset($this->payload[self::not_before_attribute])) {
            $this->notBefore = new \DateTimeImmutable();
            $this->notBefore->setTimestamp($this->payload[self::not_before_attribute]);
        }
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->tokenString ?? $this->tokenString = $this->encoder->encode($this);
    }
}
