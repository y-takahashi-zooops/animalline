<?php

namespace Customize\Controller\Breeder;

use Customize\Config\AnilineConf;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Customize\Repository\BreedersRepository;
use Eccube\Controller\AbstractController;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BreederBenefitsController extends AbstractController
{

    /**
     * @var BreedersRepository
     */
    protected $breedersRepository;

    /**
     * BreederController constructor.
     *
     * @param BreedersRepository $breedersRepository
     */
    public function __construct(
        BreedersRepository               $breedersRepository
    ) {
        $this->breedersRepository = $breedersRepository;
    }

    /**
     * 特典受取手続き画面.
     *
     * @Route("/breeder/member/benefits", name="breeder_benefits")
     * @Template("animalline/breeder/member/benefits.twig")
     */
    public function benefits(Request $request)
    {
    }
}
