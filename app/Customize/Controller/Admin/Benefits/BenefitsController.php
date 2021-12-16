<?php

namespace Customize\Controller\Admin\Benefits;

use Customize\Config\AnilineConf;
use Customize\Repository\BenefitsStatusRepository;
use Eccube\Controller\AbstractController;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
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
        $results = $this->benefitsStatusRepository->filterBenefitAdmin($request->query->all());
        $Benefits = $paginator->paginate(
            $results,
            $request->query->getInt('page', 1),
            $request->query->getInt('item', AnilineConf::ANILINE_NUMBER_ITEM_PER_PAGE_ADMIN)
        );
        return [
            'benefits' => $Benefits
        ];
    }
}
