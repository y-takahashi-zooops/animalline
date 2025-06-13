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

    public function verify(string $hashedPassword, string $plainPassword, ?object $user = null): bool
    {
        $this->logger->info('LegacyHasherが呼ばれた');
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
