<?php
namespace Customize\Security\Http\Authentication;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationSuccessHandler;

class EccubeLoginSuccessHandler extends DefaultAuthenticationSuccessHandler
{
    /**
     * {@inheritdoc}
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token): ?Response
    {
        $response = parent::onAuthenticationSuccess($request, $token);

        // 呼び出し元に戻る
        //$referer = $request->headers->get('referer');
        //$response->setTargetUrl($referer);

        if (preg_match('/^https?:\\\\/i', $response->getTargetUrl())) {
            $response->setTargetUrl($request->getUriForPath('/'));
        }

        return $response;
    }
}
