<?php

namespace App\Security\Authenticator;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authenticator\FormLoginAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class CustomFormLoginAuthenticator extends FormLoginAuthenticator
{
    protected function getLoginUrl(Request $request): string
    {
        return $this->getUrlGenerator()->generate('adoption_login');
    }

    protected function getDefaultSuccessRedirectUrl(Request $request): string
    {
        return '/adoption_mypage';
    }

    public function authenticate(Request $request): Passport
    {
        $email = $request->request->get('login_email', '');
        $password = $request->request->get('login_pass', '');
        $csrfToken = $request->request->get('_csrf_token');

        $request->getSession()->set('_security.last_username', $email);

        return new Passport(
            new \Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge(
                $email,
                function ($userIdentifier) use ($password) {
                    // このクロージャ内で UserProvider 経由で User を取得
                    return $this->userProvider->loadUserByIdentifier($userIdentifier);
                }
            ),
            new PasswordCredentials($password),
            [
                new CsrfTokenBadge('authenticate', $csrfToken),
            ]
        );
    }
}
