<?php

namespace App\Hasher;

use Symfony\Component\PasswordHasher\PasswordHasherInterface;
use Eccube\Entity\Customer;

class LegacyCustomerPasswordHasher implements PasswordHasherInterface
{
    public function hash(string $plainPassword): string
    {
        throw new \LogicException('hash() should not be called directly.');
    }

    public function verify(string $hashedPassword, string $plainPassword, ?object $user = null): bool
    {
        if (!$user instanceof Customer) {
            return false;
        }

        $secretKey = $user->getSecretKey(); // ← これ重要
        $expected = hash('sha256', $secretKey . $plainPassword);

        return hash_equals($hashedPassword, $expected);
    }

    public function needsRehash(string $hashedPassword): bool
    {
        return true;
    }
}
