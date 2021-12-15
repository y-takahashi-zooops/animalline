<?php

namespace Customize\Controller\Admin\Benefits;

use Carbon\Carbon;
use Customize\Config\AnilineConf;
use Customize\Entity\DnaCheckKinds;
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
     * BenefitsController constructor
     */
    public function __construct(
    ) {
    }

    /**
     * 特典発送状況管理画面.
     *
     * @Route("/%eccube_admin_route%/benefits/delivery_status", name="admin_benefits_delivery_status")
     * @Template("@admin/Benefits/index.twig")
     */
    public function index(PaginatorInterface $paginator, Request $request)
    {
      return[];
    }
}
