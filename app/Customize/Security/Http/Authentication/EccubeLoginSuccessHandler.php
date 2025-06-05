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
    protected $logger;
    public function __construct(HttpUtils $httpUtils, LoggerInterface $logger,array $options = [])
    {
        parent::__construct($httpUtils, $options);
        $this->logger = $logger;
        // parent::__construct($httpUtils, $logger, $options);
    }
    /**
     * {@inheritdoc}
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token): ?Response
    {
        dd("さくせすはんどら");
        throw new \RuntimeException('Custom SuccessHandler was called!');
        
        // file_put_contents('/tmp/login_success.log', date('Y-m-d H:i:s') . " - Login success handler called\n", FILE_APPEND);
        // $this->logger->info('EccubeLoginSuccessHandler: ログイン成功');

        // $targetPath = $this->defaultOptions['default_target_path'] ?? '/';
        // $this->logger->info('Redirecting to: ' . $targetPath);

        // return $this->httpUtils->createRedirectResponse($request, '/admin/');

        // return $this->httpUtils->createRedirectResponse($request, $targetPath);
    }
}
