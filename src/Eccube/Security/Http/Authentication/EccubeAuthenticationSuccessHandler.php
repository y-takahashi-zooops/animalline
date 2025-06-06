<?php

/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) EC-CUBE CO.,LTD. All Rights Reserved.
 *
 * http://www.ec-cube.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eccube\Security\Http\Authentication;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationSuccessHandler;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class EccubeAuthenticationSuccessHandler extends DefaultAuthenticationSuccessHandler
{
    public function __construct(HttpKernelInterface $httpKernel, HttpUtils $httpUtils, array $options = [])
    {
        parent::__construct($httpKernel, $httpUtils, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token): ?Response
    {
        // 親クラスの onAuthenticationSuccess メソッドを呼び出す
        $response = parent::onAuthenticationSuccess($request, $token);

        // TargetUrlがnullでないことを確認し、適切なURLを設定する
        if ($response instanceof Response) {
            $targetUrl = $response->headers->get('Location');
            if ($targetUrl && preg_match('/^https?:\/\//i', $targetUrl)) {
                # $response->headers->set('Location', $request->getUriForPath('/admin/'));
                $response->headers->set('Location', $request->getSchemeAndHttpHost() . $request->getBaseUrl() . '/%eccube_admin_route%/');
            }
        }
        return $response;
    }
}
