<?php
namespace Customize\Security\Http\Authentication;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationSuccessHandler;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class EccubeLoginSuccessHandler extends DefaultAuthenticationSuccessHandler
{
    public function __construct(HttpUtils $httpUtils, array $options = [])
    {
        parent::__construct($httpUtils, $options);
    }
    /**
     * {@inheritdoc}
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token): ?Response
    {
        // $response = parent::onAuthenticationSuccess($request, $token);

        // // 呼び出し元に戻る
        // //$referer = $request->headers->get('referer');
        // //$response->setTargetUrl($referer);

        // if (preg_match('/^https?:\\\\/i', $response->getTargetUrl())) {
        //     $response->setTargetUrl($request->getUriForPath('/%eccube_admin_route%/'));
        // }

        // return $response;
        
        // 管理画面（eccube_admin_route）に強制リダイレクト
        return $this->httpUtils->createRedirectResponse(
            $request,
            $request->getBaseUrl() . '/%eccube_admin_route%/' // Symfonyが %eccube_admin_route% をパラメータ展開します
        );
    }
}
