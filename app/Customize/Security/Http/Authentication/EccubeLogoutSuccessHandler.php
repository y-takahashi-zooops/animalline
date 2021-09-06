<?php

namespace Customize\Security\Http\Authentication;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;

class EccubeLogoutSuccessHandler implements LogoutSuccessHandlerInterface
{

    public function onLogoutSuccess(\Symfony\Component\HttpFoundation\Request $request)
    {
        $referer = $request->headers->get('referer');

        return new RedirectResponse($referer);
    }
}
