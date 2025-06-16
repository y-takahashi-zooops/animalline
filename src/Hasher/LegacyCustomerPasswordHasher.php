<?php

namespace App\Hasher;

use Symfony\Component\PasswordHasher\PasswordHasherInterface;
use Eccube\Entity\Customer;
use Psr\Log\LoggerInterface;

class LegacyCustomerPasswordHasher implements PasswordHasherInterface
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function hash(string $plainPassword): string
    {
        // legacy hasher is verify-only; should never be used to hash
        throw new \LogicException('LegacyCustomerPasswordHasher does not support hashing.');
    }

    public function verify(string $hashedPassword, string $plainPassword, ?object $user = null): bool
    {
        // EC-CUBEの旧仕様: SHA256(secret_key + password) を前提としない
        // $expected = hash('sha256', 'V4waqscSDx1VYMe1lgW74KUCNO2SaZEh' . $plainPassword);
        $expected = hash('sha256', $plainPassword);

        dd('試しにdd!',$hashedPassword, $plainPassword, $expected, hash_equals($hashedPassword, $expected));
        if (is_object($user)) {
            $this->logger->info('LegacyHasher: user class = ' . get_class($user));
        } else {
            $this->logger->info('LegacyHasher: user is not an object');
        }

        if (!$user instanceof \Eccube\Entity\Customer) {
            $this->logger->info('LegacyHasher: user is not Customer');
            return false;
        }

        $secretKey = $user->getSecretKey();
        $expected = hash('sha256', $secretKey . $plainPassword);
        $this->logger->info('LegacyHasher: checking password', [
            'expected' => $expected,
            'actual' => $hashedPassword,
        ]);

        return hash_equals($hashedPassword, $expected);
    }

    public function needsRehash(string $hashedPassword): bool
    {
        return true;
    }
}
