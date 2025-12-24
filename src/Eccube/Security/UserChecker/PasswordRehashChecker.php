<?php
namespace Eccube\Security\UserChecker;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class PasswordRehashChecker implements UserCheckerInterface
{
    private $hasher;
    private $em;

    public function __construct(UserPasswordHasherInterface $hasher, EntityManagerInterface $em)
    {
        $this->hasher = $hasher;
        $this->em = $em;
    }

    public function checkPreAuth(UserInterface $user): void
    {
        // no-op
    }

    public function checkPostAuth(UserInterface $user): void
    {
        if ($this->hasher->needsRehash($user)) {
            $plainPassword = $user->getPlainPassword(); // 事前に設定されている必要あり
            if ($plainPassword) {
                $hashed = $this->hasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashed);
                $this->em->persist($user);
                $this->em->flush();
            }
        }
    }
}
