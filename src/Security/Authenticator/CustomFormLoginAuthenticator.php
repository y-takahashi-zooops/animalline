<?php

namespace App\Security\Authenticator;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Component\Routing\RouterInterface;
use App\Security\Authenticator\Response;

class CustomFormLoginAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    private string $loginUrl;

    public function __construct(string $loginUrl)
    {
        $this->loginUrl = $loginUrl;
    }

    public function authenticate(Request $request): Passport
    {
        $email = $request->request->get('login_email', '');
        $password = $request->request->get('login_pass', '');
        $csrfToken = $request->request->get('_csrf_token');

        return new Passport(
            new UserBadge($email),
            new PasswordCredentials($password),
            [
                new CsrfTokenBadge('authenticate', $csrfToken),
                new RememberMeBadge(),
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, $token, string $firewallName): ?Response
    {
        // ログイン成功後のリダイレクト処理などを記述
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->loginUrl;
    }
}
