<?php
namespace Customize\Security\Http\Authentication;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationSuccessHandler;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\User\UserInterface;

class EccubeLoginSuccessHandler extends DefaultAuthenticationSuccessHandler
{
    protected $hasher;
    protected $em;

    public function __construct(
        HttpUtils $httpUtils,
        array $options = [],
        UserPasswordHasherInterface $hasher,
        EntityManagerInterface $em
    ) {
        parent::__construct($httpUtils, $options);
        $this->hasher = $hasher;
        $this->em = $em;
    }
    /**
     * {@inheritdoc}
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token): ?Response
    {
        /** @var UserInterface $user */
        $user = $token->getUser();

        // plainPassword が Request から来ている前提
        // $plainPassword = $request->request->get('_password');
        // dd($user,$plainPassword, $this->hasher->needsRehash($user));
        // if ($plainPassword && $this->hasher->needsRehash($user)) {
        //     $hashedPassword = $this->hasher->hashPassword($user, $plainPassword);
        //     $user->setPassword($hashedPassword);
        //     $this->em->persist($user);
        //     $this->em->flush();
        // }

        // $targetPath = $this->defaultOptions['default_target_path'] ?? '/';
        $targetPath = $this->options['default_target_path'] ?? '/';
        return $this->httpUtils->createRedirectResponse($request, $targetPath);
    }
}
