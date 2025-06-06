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
    protected $logger;
    public function __construct(HttpUtils $httpUtils,array $options = [])
    {
        parent::__construct($httpUtils, $options);
    }
    /**
     * {@inheritdoc}
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token): ?Response
    {
        $targetPath = $this->defaultOptions['default_target_path'] ?? '/';
        return $this->httpUtils->createRedirectResponse($request, $targetPath);
    }
}
