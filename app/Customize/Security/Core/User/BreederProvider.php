<?php

namespace Customize\Security\Core\User;

use Customize\Config\AnilineConf;
use Customize\Entity\Breeders;
use Customize\Repository\BreedersRepository;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class BreederProvider implements UserProviderInterface
{
    /**
     * @var BreedersRepository
     */
    protected $breedersRepository;

    public function __construct(BreedersRepository $breedersRepository)
    {
        $this->breedersRepository = $breedersRepository;
    }

    /**
     * Loads the user for the given username.
     *
     * This method must throw UsernameNotFoundException if the user is not
     * found.
     *
     * @param string $username The username
     *
     * @return UserInterface
     *
     * @throws UsernameNotFoundException if the user is not found
     */
    public function loadUserByUsername($username)
    {
        $Breeders = $this->breedersRepository->findOneBy([
            'email' => $username,
            'register_status_id' => AnilineConf::ANILINE_REGISTER_STATUS_ACTIVE,
        ]);

        if (null === $Breeders) {
            throw new UsernameNotFoundException(sprintf('Username "%s" does not exist.', $username));
        }

        return $Breeders;
    }

    /**
     * Refreshes the user.
     *
     * It is up to the implementation to decide if the user data should be
     * totally reloaded (e.g. from the database), or if the UserInterface
     * object can just be merged into some internal array of users / identity
     * map.
     *
     * @return UserInterface
     *
     * @throws UnsupportedUserException if the user is not supported
     */
    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof Breeders) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    /**
     * Whether this provider supports the given user class.
     *
     * @param string $class
     *
     * @return bool
     */
    public function supportsClass($class)
    {
        return Breeders::class === $class;
    }
}
