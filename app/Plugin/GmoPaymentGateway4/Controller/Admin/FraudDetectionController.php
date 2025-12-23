<?php

/*
 * Copyright(c) 2022 GMO Payment Gateway, Inc. All rights reserved.
 * http://www.gmo-pg.com/
 */

namespace Plugin\GmoPaymentGateway4\Controller\Admin;

use Eccube\Common\Constant;
use Eccube\Controller\AbstractController;
use Eccube\Repository\Master\PageMaxRepository;
use Eccube\Util\FormUtil;
use Knp\Component\Pager\PaginatorInterface;
use Plugin\GmoPaymentGateway4\Form\Type\Admin\SearchFraudDetectionType;
use Plugin\GmoPaymentGateway4\Repository\GmoFraudDetectionRepository;
use Plugin\GmoPaymentGateway4\Service\FraudDetector;
use Plugin\GmoPaymentGateway4\Service\Method\CreditCard;
use Plugin\GmoPaymentGateway4\Util\PaymentUtil;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use Eccube\Common\EccubeConfig;

class FraudDetectionController extends AbstractController
{
    /**
     * @var PageMaxRepository
     */
    protected $pageMaxRepository;

    /**
     * @var GmoFraudDetectionRepository
     */
    protected $gmoFraudDetectionRepository;

    /**
     * @var FraudDetector
     */
    protected $fraudDetector;

    public function __construct(
        PageMaxRepository $pageMaxRepository,
        GmoFraudDetectionRepository $gmoFraudDetectionRepository,
        FraudDetector $fraudDetector,
        EccubeConfig $eccubeConfig
    ) {
        $this->pageMaxRepository = $pageMaxRepository;
        $this->gmoFraudDetectionRepository = $gmoFraudDetectionRepository;
        $this->fraudDetector = $fraudDetector;
        $this->eccubeConfig = $eccubeConfig;

        // 不正検知機能を初期化
        $this->fraudDetector->initPaymentMethodClass(CreditCard::class);
    }

    /**
     * @Route("/%eccube_admin_route%/gmo_payment_gateway/fraud_detection", name="gmo_payment_gateway_admin_fraud_detection")
     * @Route("/%eccube_admin_route%/gmo_payment_gateway/fraud_detection/page/{page_no}", requirements={"page_no" = "\d+"}, name="gmo_payment_gateway_admin_fraud_detection_page")
     * @Template("@GmoPaymentGateway4/admin/fraud_detection_index.twig")
     */
    public function index
        (Request $request, PaginatorInterface $paginator, ?int $page_no = null)
    {
        $session = $this->session;
        $builder = $this->formFactory
            ->createBuilder(SearchFraudDetectionType::class);

        $searchForm = $builder->getForm();

        $pageMaxis = $this->pageMaxRepository->findAll();
        $pageCount = $session->get
            ('gmo_payment_gateway.admin.fraud_detection.search.page_count',
             $this->eccubeConfig['eccube_default_page_count']);
        $pageCountParam = $request->get('page_count');
        if ($pageCountParam && is_numeric($pageCountParam)) {
            foreach ($pageMaxis as $pageMax) {
                if ($pageCountParam == $pageMax->getName()) {
                    $pageCount = $pageMax->getName();
                    $session->set
                        ('gmo_payment_gateway.admin.' .
                         'fraud_detection.search.page_count',
                         $pageCount);
                    break;
                }
            }
        }

        if ('POST' === $request->getMethod()) {
            $searchForm->handleRequest($request);
            if ($searchForm->isSubmitted() && $searchForm->isValid()) {
                $searchData = $searchForm->getData();
                $page_no = 1;

                $session->set
                    ('gmo_payment_gateway.admin.fraud_detection.search',
                     FormUtil::getViewData($searchForm));
                $session->set
                    ('gmo_payment_gateway.admin.' .
                     'fraud_detection.search.page_no',
                     $page_no);
            } else {
                return [
                    'FraudDetector' => $this->fraudDetector,
                    'searchForm' => $searchForm->createView(),
                    'pagination' => [],
                    'pageMaxis' => $pageMaxis,
                    'page_no' => $page_no,
                    'page_count' => $pageCount,
                    'has_errors' => true,
                ];
            }
        } else {
            if (null !== $page_no || $request->get('resume')) {
                if ($page_no) {
                    $session->set
                        ('gmo_payment_gateway.admin.' .
                         'fraud_detection.search.page_no',
                         (int) $page_no);
                } else {
                    $page_no = $session->get
                        ('gmo_payment_gateway.admin.' .
                         'fraud_detection.search.page_no', 1);
                }
                $viewData = $session->get
                    ('gmo_payment_gateway.admin.fraud_detection.search', []);
            } else {
                $page_no = 1;
                $viewData = FormUtil::getViewData($searchForm);
                $session->set
                    ('gmo_payment_gateway.admin.fraud_detection.search',
                     $viewData);
                $session->set
                    ('gmo_payment_gateway.admin.' .
                     'fraud_detection.search.page_no',
                     $page_no);
            }
            $searchData = FormUtil::submitAndGetData($searchForm, $viewData);
        }

        /** @var QueryBuilder $qb */
        $qb = $this->gmoFraudDetectionRepository
            ->getQueryBuilderBySearchData($searchData);

        $pagination = $paginator->paginate(
            $qb,
            $page_no,
            $pageCount
        );

        return [
            'FraudDetector' => $this->fraudDetector,
            'searchForm' => $searchForm->createView(),
            'pagination' => $pagination,
            'pageMaxis' => $pageMaxis,
            'page_no' => $page_no,
            'page_count' => $pageCount,
            'has_errors' => false,
        ];
    }

    /**
     * 指定IPアドレスのロックを解除する
     *
     * @Route("/%eccube_admin_route%/gmo_payment_gateway/fraud_detection/unlock/{ipAddr}", requirements={"ipAddr" = "[0-9\.]+"}, name="gmo_payment_gateway_admin_fraud_detection_unlock", methods={"POST"})
     *
     * @param Request $request
     * @param string $ipAddr IPアドレス
     */
    public function unlock(Request $request, $ipAddr)
    {
        PaymentUtil::logInfo(__METHOD__ . ' start. [' . $ipAddr . ']');

        $this->isTokenValid();

        $this->fraudDetector->unlock($ipAddr);
        $this->addSuccess(trans(
            'gmo_payment_gateway.admin.' .
            'fraud_detection.unlock.done',
            ['%ip%' => $ipAddr]),
        'admin');

        $page_no = intval($this->session->get
                          ('gmo_payment_gateway.admin.' .
                           'fraud_detection.search.page_no'));
        $page_no = $page_no ? $page_no : Constant::ENABLED;

        PaymentUtil::logInfo(__METHOD__ . ' end.');

        return $this->redirect
            ($this->generateUrl('gmo_payment_gateway_admin_' .
                                'fraud_detection_page',
                                ['page_no' => $page_no]) .
             '?resume=' . Constant::ENABLED);
    }
}
