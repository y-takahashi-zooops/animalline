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

namespace Customize\Controller\Admin\Customer;

use Customize\Config\AnilineConf;
use Customize\Repository\BreederPetsRepository;
use Customize\Repository\BreedersRepository;
use Customize\Repository\ConservationPetsRepository;
use Customize\Repository\ConservationsRepository;
use Customize\Service\CustomerQueryService;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\QueryBuilder;
use Eccube\Common\Constant;
use Eccube\Controller\AbstractController;
use Eccube\Entity\Master\CsvType;
use Eccube\Entity\Master\CustomerStatus;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Eccube\Form\Type\Admin\SearchCustomerType;
use Eccube\Repository\CustomerRepository;
use Eccube\Repository\Master\CustomerStatusRepository;
use Eccube\Repository\Master\PageMaxRepository;
use Eccube\Repository\Master\PrefRepository;
use Eccube\Repository\Master\SexRepository;
use Eccube\Service\CsvExportService;
use Customize\Service\MailService;
use Eccube\Util\FormUtil;
use Knp\Component\Pager\Paginator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Eccube\Common\EccubeConfig;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\RequestStack;


class CustomerController extends AbstractController
{
    /**
     * @var CsvExportService
     */
    protected $csvExportService;

    /**
     * @var MailService
     */
    protected $mailService;

    /**
     * @var PrefRepository
     */
    protected $prefRepository;

    /**
     * @var SexRepository
     */
    protected $sexRepository;

    /**
     * @var PageMaxRepository
     */
    protected $pageMaxRepository;

    /**
     * @var CustomerRepository
     */
    protected $customerRepository;

    /**
     * @var BreedersRepository
     */
    protected $breedersRepository;

    /**
     * @var ConservationsRepository
     */
    protected $conservationsRepository;

    /**
     * @var ConservationPetsRepository
     */
    protected $conservationPetsRepository;

    /**
     * @var BreederPetsRepository
     */
    protected $breederPetsRepository;

    /**
     * @var CustomerStatusRepository
     */
    protected $customerStatusRepository;

    /**
     * @var CustomerQueryService
     */
    protected $customerQueryService;

    /**
     * @var LoggerInterface
     * @param SessionInterface $session,
     */
    protected $logger;

    public function __construct(
        PageMaxRepository $pageMaxRepository,
        CustomerRepository $customerRepository,
        SexRepository $sexRepository,
        PrefRepository $prefRepository,
        MailService $mailService,
        CsvExportService $csvExportService,
        BreedersRepository $breedersRepository,
        ConservationsRepository $conservationsRepository,
        ConservationPetsRepository $conservationPetsRepository,
        BreederPetsRepository $breederPetsRepository,
        CustomerStatusRepository $customerStatusRepository,
        CustomerQueryService $customerQueryService,
        LoggerInterface $logger,
        EventDispatcherInterface $eventDispatcher,
        EccubeConfig $eccubeConfig,
        EntityManagerInterface $entityManager,
        RequestStack $requestStack
    ) {
        $this->pageMaxRepository = $pageMaxRepository;
        $this->customerRepository = $customerRepository;
        $this->sexRepository = $sexRepository;
        $this->prefRepository = $prefRepository;
        $this->mailService = $mailService;
        $this->csvExportService = $csvExportService;
        $this->breedersRepository = $breedersRepository;
        $this->conservationsRepository = $conservationsRepository;
        $this->conservationPetsRepository = $conservationPetsRepository;
        $this->breederPetsRepository = $breederPetsRepository;
        $this->customerStatusRepository = $customerStatusRepository;
        $this->customerQueryService = $customerQueryService;
        $this->logger = $logger;
        $this->eventDispatcher = $eventDispatcher;
        $this->eccubeConfig = $eccubeConfig;
        $this->entityManager = $entityManager;
        $this->requestStack = $requestStack;
    }

    /**
     * @Route("/%eccube_admin_route%/customer", name="admin_customer")
     * @Route("/%eccube_admin_route%/customer/page/{page_no}", requirements={"page_no" = "\d+"}, name="admin_customer_page")
     * @Template("@admin/Customer/index.twig")
     */
    public function index(Request $request, ?int $page_no = 1, PaginatorInterface $paginator)
    {
        $session = $this->session;
        $builder = $this->formFactory->createBuilder(SearchCustomerType::class);

        $event = new EventArgs(
            [
                'builder' => $builder,
            ],
            $request
        );
        $this->eventDispatcher->dispatch($event, EccubeEvents::ADMIN_CUSTOMER_INDEX_INITIALIZE);

        $searchForm = $builder->getForm();

        $pageMaxis = $this->pageMaxRepository->findAll();
        $pageCount = $session->get('eccube.admin.customer.search.page_count', $this->eccubeConfig['eccube_default_page_count']);
        $pageCountParam = $request->get('page_count');
        if ($pageCountParam && is_numeric($pageCountParam)) {
            foreach ($pageMaxis as $pageMax) {
                if ($pageCountParam == $pageMax->getName()) {
                    $pageCount = $pageMax->getName();
                    $session->set('eccube.admin.customer.search.page_count', $pageCount);
                    break;
                }
            }
        }

        if ('POST' === $request->getMethod()) {
            $searchForm->handleRequest($request);
            if ($searchForm->isValid()) {
                $searchData = $searchForm->getData();
                $page_no = 1;

                $session->set('eccube.admin.customer.search', FormUtil::getViewData($searchForm));
                $session->set('eccube.admin.customer.search.page_no', $page_no);
            } else {
                return [
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
                    $session->set('eccube.admin.customer.search.page_no', (int) $page_no);
                } else {
                    $page_no = $session->get('eccube.admin.customer.search.page_no', 1);
                }
                $viewData = $session->get('eccube.admin.customer.search', []);
            } else {
                $page_no = 1;
                $viewData = FormUtil::getViewData($searchForm);
                $session->set('eccube.admin.customer.search', $viewData);
                $session->set('eccube.admin.customer.search.page_no', $page_no);
            }
            $searchData = FormUtil::submitAndGetData($searchForm, $viewData);
        }

        /** @var QueryBuilder $qb */
        $qb = $this->customerRepository->getQueryBuilderBySearchData($searchData);

        $event = new EventArgs(
            [
                'form' => $searchForm,
                'qb' => $qb,
            ],
            $request
        );
        $this->eventDispatcher->dispatch($event, EccubeEvents::ADMIN_CUSTOMER_INDEX_SEARCH);

        $pagination = $paginator->paginate(
            $qb,
            $page_no,
            $pageCount
        );

        return [
            'searchForm' => $searchForm->createView(),
            'pagination' => $pagination,
            'pageMaxis' => $pageMaxis,
            'page_no' => $page_no,
            'page_count' => $pageCount,
            'has_errors' => false,
        ];
    }

    /**
     * @Route("/%eccube_admin_route%/customer/{id}/resend", requirements={"id" = "\d+"}, name="admin_customer_resend")
     */
    public function resend(Request $request, $id)
    {
        $this->isTokenValid();

        $Customer = $this->customerRepository
            ->find($id);

        if (is_null($Customer)) {
            throw new NotFoundHttpException();
        }

        if($Customer->getRegistType() == 1){
            $activateUrl = $this->generateUrl(
                'entry_activate',
                ['secret_key' => $Customer->getSecretKey(), 'returnPath' => "breeder_mypage"],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
        }
        elseif($Customer->getRegistType() == 2){
            $activateUrl = $this->generateUrl(
                'entry_activate',
                ['secret_key' => $Customer->getSecretKey(), 'returnPath' => "adoption_mypage"],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
        }
        else{
            $activateUrl = $this->generateUrl(
                'entry_activate',
                ['secret_key' => $Customer->getSecretKey(), 'returnPath' => "homepage"],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
        }

        // メール送信
        $this->mailService->sendAdminCustomerConfirmMail($Customer, $activateUrl);

        $event = new EventArgs(
            [
                'Customer' => $Customer,
                'activateUrl' => $activateUrl,
            ],
            $request
        );
        $this->eventDispatcher->dispatch($event, EccubeEvents::ADMIN_CUSTOMER_RESEND_COMPLETE);

        $this->addSuccess('admin.common.send_complete', 'admin');

        return $this->redirectToRoute('admin_customer');
    }

    /**
     * @Route("/%eccube_admin_route%/customer/{id}/delete", requirements={"id" = "\d+"}, name="admin_customer_delete", methods={"DELETE"})
     */
    public function delete(Request $request, $id, TranslatorInterface $translator)
    {
        $this->isTokenValid();

        $this->logger->info('会員削除開始', [$id]);

        $page_no = intval($this->session->get('eccube.admin.customer.search.page_no'));
        $page_no = $page_no ? $page_no : Constant::ENABLED;

        $Customer = $this->customerRepository
            ->find($id);

        if (!$Customer) {
            $this->deleteMessage();

            return $this->redirect($this->generateUrl(
                'admin_customer_page',
                ['page_no' => $page_no]
            ) . '?resume=' . Constant::ENABLED);
        }
        try {
            if ($Customer->getStatus()->getId() === CustomerStatus::PROVISIONAL) {
                $this->entityManager->remove($Customer);
            } else {
                $status = $this->customerStatusRepository->find(CustomerStatus::WITHDRAWING);
                $Customer->setStatus($status);
                $this->entityManager->persist($Customer);

                if ($breeder = $this->breedersRepository->find($Customer->getId())) {
                    $breeder->setExaminationStatus(AnilineConf::EXAMINATION_STATUS_CUSTOMER_DELETED);
                    $breederPets = $this->breederPetsRepository->findBy(['Breeder' => $breeder]);
                    foreach ($breederPets as $breederPet) {
                        $breederPet->setIsDelete(true)
                            ->setIsActive(AnilineConf::IS_ACTIVE_PRIVATE);
                        $this->entityManager->persist($breederPet);
                    }
                    $this->entityManager->persist($breeder);
                }

                if ($conservation = $this->conservationsRepository->find($Customer->getId())) {
                    $conservation->setExaminationStatus(AnilineConf::EXAMINATION_STATUS_CUSTOMER_DELETED);
                    $conservationPets = $this->conservationPetsRepository->findBy(['Conservation' => $conservation]);
                    foreach ($conservationPets as $conservationPet) {
                        $conservationPet->setIsDelete(true)
                            ->setIsActive(AnilineConf::IS_ACTIVE_PRIVATE);
                        $this->entityManager->persist($conservationPet);
                    }
                    $this->entityManager->persist($conservation);
                }
            }

            $this->entityManager->flush();
            $this->addSuccess('admin.common.delete_complete', 'admin');
        } catch (ForeignKeyConstraintViolationException $e) {
            log_error('会員削除失敗', [$e], 'admin');

            $message = trans('admin.common.delete_error_foreign_key', ['%name%' => $Customer->getName01() . ' ' . $Customer->getName02()]);
            $this->addError($message, 'admin');
        }

        $this->logger->info('会員削除完了', [$id]);

        $event = new EventArgs(
            [
                'Customer' => $Customer,
            ],
            $request
        );
        $this->eventDispatcher->dispatch($event, EccubeEvents::ADMIN_CUSTOMER_DELETE_COMPLETE);

        return $this->redirect($this->generateUrl(
            'admin_customer_page',
            ['page_no' => $page_no]
        ) . '?resume=' . Constant::ENABLED);
    }

    /**
     * 会員CSVの出力.
     *
     * @Route("/%eccube_admin_route%/customer/export", name="admin_customer_export")
     *
     * @param Request $request
     *
     * @return StreamedResponse
     */
    public function export(Request $request)
    {
        // タイムアウトを無効にする.
        set_time_limit(0);

        // sql loggerを無効にする.
        $em = $this->entityManager;
        $em->getConfiguration()->setSQLLogger(null);

        $response = new StreamedResponse();
        $response->setCallback(function () use ($request) {
            // CSV種別を元に初期化.
            $this->csvExportService->initCsvType(CsvType::CSV_TYPE_CUSTOMER);

            // ヘッダ行の出力.
            $this->csvExportService->exportHeader();

            // 会員データ検索用のクエリビルダを取得.
            $qb = $this->csvExportService
                ->getCustomerQueryBuilder($request);

            // データ行の出力.
            $this->csvExportService->setExportQueryBuilder($qb);
            $this->csvExportService->exportData(function ($entity, $csvService) use ($request) {
                $Csvs = $csvService->getCsvs();

                /** @var $Customer \Eccube\Entity\Customer */
                $Customer = $entity;

                $ExportCsvRow = new \Eccube\Entity\ExportCsvRow();

                // CSV出力項目と合致するデータを取得.
                foreach ($Csvs as $Csv) {
                    // 会員データを検索.
                    $ExportCsvRow->setData($csvService->getData($Csv, $Customer));

                    $event = new EventArgs(
                        [
                            'csvService' => $csvService,
                            'Csv' => $Csv,
                            'Customer' => $Customer,
                            'ExportCsvRow' => $ExportCsvRow,
                        ],
                        $request
                    );
                    $this->eventDispatcher->dispatch($event, EccubeEvents::ADMIN_CUSTOMER_CSV_EXPORT);

                    $ExportCsvRow->pushData();
                }

                //$row[] = number_format(memory_get_usage(true));
                // 出力.
                $csvService->fputcsv($ExportCsvRow->getRow());
            });
        });

        $now = new \DateTime();
        $filename = 'customer_' . $now->format('YmdHis') . '.csv';
        $response->headers->set('Content-Type', 'application/octet-stream');
        $response->headers->set('Content-Disposition', 'attachment; filename=' . $filename);

        $response->send();

        $this->logger->info('会員CSVファイル名', [$filename]);

        return $response;
    }

    /**
     * @Route("/%eccube_admin_route%/monthly-invoice", name="admin_monthly_invoice")
     * @Template("@admin/Customer/monthly_invoice.twig")
     */
    public function MonthlyInvoice(Request $request, PaginatorInterface $paginator)
    {
        $listMonthlyInvoice = $paginator->paginate(
            $this->customerQueryService->getMonthlyInvoice($request),
            $request->query->getInt('page', 1),
            $request->query->getInt('item', AnilineConf::ANILINE_NUMBER_ITEM_PER_PAGE_ADMIN)
        );
        return [
            'listMonthlyInvoice' => $listMonthlyInvoice
        ];
    }
}
