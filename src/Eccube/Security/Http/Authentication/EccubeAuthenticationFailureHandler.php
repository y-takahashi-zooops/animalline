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
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationFailureHandler;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

class EccubeAuthenticationFailureHandler extends DefaultAuthenticationFailureHandler
{
    /**
     * {@inheritdoc}
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        $response = parent::onAuthenticationFailure($request, $exception);

//        if (preg_match('/^https?:\\\\/i', $response->getTargetUrl())) {
//            $response->setTargetUrl($request->getUriForPath('/'));
//        }
        if ($response instanceof RedirectResponse) {
            // URLが http:// または https:// で始まる場合、リダイレクト先を変更
            if (preg_match('/^https?:\/\//i', $response->getTargetUrl())) {
                // 新しいリダイレクト先を設定
                $response->setTargetUrl($request->getUriForPath('/admin_homepage'));
            }
        }
        return $response;
    }
}
