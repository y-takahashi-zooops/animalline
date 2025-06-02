<?php
namespace Customize\Security\Http\Authentication;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationSuccessHandler;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Psr\Log\LoggerInterface;

class EccubeLoginSuccessHandler extends DefaultAuthenticationSuccessHandler
{
    private $logger;
    public function __construct(HttpUtils $httpUtils, array $options = [], LoggerInterface $logger)
    {
        parent::__construct($httpUtils, $options);
        $this->logger = $logger;
    }
    /**
     * {@inheritdoc}
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token): ?Response
    {
        $this->logger->info('EccubeLoginSuccessHandler: ログイン成功');

        $targetPath = $request->getBaseUrl() . '/%eccube_admin_route%/';
        $this->logger->info('Redirecting to: ' . $targetPath);

        return $this->httpUtils->createRedirectResponse($request, $targetPath);
    }
}
