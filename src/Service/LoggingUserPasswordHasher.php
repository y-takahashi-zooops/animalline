<?php

namespace App\Service;

use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Psr\Log\LoggerInterface;

class LoggingUserPasswordHasher implements UserPasswordHasherInterface
{
    private $inner;
    private $logger;

    public function __construct(UserPasswordHasherInterface $inner, LoggerInterface $logger)
    {
        $this->inner = $inner;
        $this->logger = $logger;
    }

    public function hashPassword(PasswordAuthenticatedUserInterface $user, string $plainPassword): string
    {
        $this->logger->info('LoggingUserPasswordHasher::hashPassword called');
        return $this->inner->hashPassword($user, $plainPassword);
    }

    public function isPasswordValid(PasswordAuthenticatedUserInterface $user, string $plainPassword): bool
    {
        $this->logger->info('LoggingUserPasswordHasher::isPasswordValid called for ' . get_class($user));
        return $this->inner->isPasswordValid($user, $plainPassword);
    }

    public function needsRehash(PasswordAuthenticatedUserInterface $user): bool
    {
        return $this->inner->needsRehash($user);
    }
}
