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
        if (!$user instanceof \Eccube\Entity\Customer) {
            dump('Not a Customer', $user); // ← デバッグ用
            return false;
        }

        $secretKey = $user->getSecretKey();
        dump('SecretKey:', $secretKey, 'Plain:', $plainPassword);

        $expected = hash('sha256', $secretKey . $plainPassword);
        dump('Expected:', $expected, 'Actual:', $hashedPassword);

        return hash_equals($hashedPassword, $expected);
    }

    public function needsRehash(string $hashedPassword): bool
    {
        return true;
    }
}
