<?php

namespace App\Service;

use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Constraint\StrictValidAt;
use Psr\Clock\ClockInterface;
use DateTimeImmutable;

class SecurityService
{
    private UserPasswordHasherInterface $passwordHasher;
    private Configuration $jwtConfig;
    private int $jwtExpiration;
    private ClockInterface $clock;

    public function __construct(
        UserPasswordHasherInterface $passwordHasher,
        ClockInterface $clock,
        string $jwtSecret,
        int $jwtExpiration = 3600
    ) {
        $this->passwordHasher = $passwordHasher;
        $this->clock = $clock;
        
        $this->jwtConfig = Configuration::forSymmetricSigner(
            new Sha256(),
            InMemory::plainText($jwtSecret)
        );
        $this->jwtExpiration = $jwtExpiration;
    }

    /**
     * Хэширует пароль для пользователя
     */
    public function hashPassword(PasswordAuthenticatedUserInterface $user, string $plainPassword): string
    {
        return $this->passwordHasher->hashPassword($user, $plainPassword);
    }

    /**
     * Проверяет соответствие пароля
     */
    public function verifyPassword(PasswordAuthenticatedUserInterface $user, string $plainPassword): bool
    {
        return $this->passwordHasher->isPasswordValid($user, $plainPassword);
    }

    public function generateJWTToken(array $claims, ?int $expiration = null): string
    {
        $expiration ??= $this->jwtExpiration;
        $now = new DateTimeImmutable();

        $builder = $this->jwtConfig->builder()
            ->issuedAt($now)
            ->expiresAt($now->modify("+{$expiration} seconds"));

        foreach ($claims as $name => $value) {
            $builder = $builder->withClaim($name, $value);
        }

        return $builder
            ->getToken($this->jwtConfig->signer(), $this->jwtConfig->signingKey())
            ->toString();
    }

    public function validateJWTToken(string $token): bool
    {
        try {
            $tokenObj = $this->jwtConfig->parser()->parse($token);
        } catch (\Exception $e) {
            return false;
        }

        $constraints = [
            new SignedWith($this->jwtConfig->signer(), $this->jwtConfig->signingKey()),
            new StrictValidAt($this->clock), 
        ];

        return $this->jwtConfig->validator()->validate($tokenObj, ...$constraints);
    }

    public function parseJWTToken(string $token): ?array
    {
        try {
            $tokenObj = $this->jwtConfig->parser()->parse($token);
            return $tokenObj->claims()->all();
        } catch (\Exception $e) {
            return null;
        }
    }
}