<?php

namespace Customize\Controller\Breeder;

use Customize\Config\AnilineConf;
use Customize\Service\BreederQueryService;
use Eccube\Controller\AbstractController;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BreederController extends AbstractController
{
    /**
     * @var BreederQueryService
     */
    protected $breederQueryService;

    /**
     * AdoptionController constructor.
     *
     * @param BreederQueryService $breederQueryService
     */
    public function __construct(
        BreederQueryService $breederQueryService
    )
    {
        $this->breederQueryService = $breederQueryService;
    }

    /**
     * ペット検索結果.
     *
     * @Route("/breeder/pet/search/result", name="breeder_pet_search_result")
     * @Template("animalline/breeder/pet/search_result.twig")
     */
    public function petSearchResult(PaginatorInterface $paginator, Request $request): Response
    {
        $petResults = $this->breederQueryService->searchPetsResult($request);
        $pets = $paginator->paginate(
            $petResults,
            $request->query->getInt('page', 1),
            AnilineConf::ANILINE_NUMBER_ITEM_PER_PAGE
        );

        return $this->render('animalline/breeder/pet/search_result.twig', ['pets' => $pets]);
    }

    /**
     * ペット詳細.
     *
     * @Route("/breeder/pet/detail/{id}", name="breeder_pet_detail", requirements={"id" = "\d+"})
     * @Template("animalline/breeder/pet/detail.twig")
     */
    public function petDetail(Request $request)
    {
        return;
    }
}
