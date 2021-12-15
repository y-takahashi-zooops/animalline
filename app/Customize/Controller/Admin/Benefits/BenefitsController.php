<?php

namespace Customize\Controller\Admin\Benefits;

use Carbon\Carbon;
use Customize\Config\AnilineConf;
use Customize\Entity\DnaCheckKinds;
use Customize\Repository\BenefitsStatusRepository;
use Customize\Service\DnaQueryService;
use Eccube\Controller\AbstractController;
use Knp\Component\Pager\PaginatorInterface;
use phpDocumentor\Reflection\Types\AbstractList;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class BenefitsController extends AbstractController
{
    /**
     * @var BenefitsStatusRepository
     */
    protected $benefitsStatusRepository;

    /**
     * BenefitsController constructor
     */
    public function __construct(
        BenefitsStatusRepository $benefitsStatusRepository
    ) {
        $this->benefitsStatusRepository = $benefitsStatusRepository;
    }

    /**
     * 特典発送状況管理画面.
     *
     * @Route("/%eccube_admin_route%/benefits/delivery_status", name="admin_benefits_delivery_status")
     * @Template("@admin/Benefits/index.twig")
     */
    public function index(PaginatorInterface $paginator, Request $request)
    {
        $criteria = [];

        switch ($request->get('site_type')) {
            case 1:
                $criteria['site_type'] = AnilineConf::SITE_CATEGORY_BREEDER;
                break;
            case 2:
                $criteria['site_type'] = AnilineConf::SITE_CATEGORY_CONSERVATION;
                break;
            default:
                break;
        }

        if ($request->get('check_status')) {
            $criteria['check_status'] = $request->get('check_status');
        }

        if ($request->get('create_date_from')) {
            $criteria['create_date_from'] = $request->get('create_date_from');
        }
        if ($request->get('create_date_to')) {
            $criteria['create_date_to'] = $request->get('create_date_to');
        }
        if ($request->get('benefits_shipping_date_from')) {
            $criteria['benefits_shipping_date_from'] = $request->get('benefits_shipping_date_from');
        }
        if ($request->get('benefits_shipping_date_to')) {
            $criteria['benefits_shipping_date_to'] = $request->get('benefits_shipping_date_to');
        }

        $results = $this->benefitsStatusRepository->filterBenefitAdmin($criteria);
        $benefits = $paginator->paginate(
            $results,
            $request->query->getInt('page', 1),
            $request->query->getInt('item', 50)
        );
        return [
            'benefits' => $benefits
        ];
    }
}
