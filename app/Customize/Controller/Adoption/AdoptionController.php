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

namespace Customize\Controller\Adoption;

use Customize\Repository\BreedsRepository;
use Customize\Repository\CoatColorsRepository;
use Customize\Repository\ConservationPetsRepository;
use Customize\Repository\ConservationsRepository;
use Eccube\Controller\AbstractController;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception as HttpException;
use Symfony\Component\Routing\Annotation\Route;

class AdoptionController extends AbstractController
{
    /**
     * @var ConservationPetsRepository
     */
    protected $conservationPetsRepository;

    /**
     * @var ConservationRepository
     */
    protected $conservationRepository;

    /**
     * @var BreedsRepository
     */
    protected $breedsRepository;

    /**
     * @var CoatColorsRepository
     */
    protected $coatColorsRepository;

    /**
     * AdoptionController constructor.
     *
     * @param
     */
    public function __construct(
        ConservationPetsRepository $conservationPetsRepository,
        ConservationsRepository $conservationsRepository,
        BreedsRepository $breedsRepository,
        CoatColorsRepository $coatColorsRepository
    )
    {
        $this->conservationPetsRepository = $conservationPetsRepository;
        $this->conservationsRepository = $conservationsRepository;
        $this->breedsRepository = $breedsRepository;
        $this->coatColorsRepository = $coatColorsRepository;
    }

    /**
     * ペット検索画面.
     *
     * @Route("/adoption/pet/search", name="adoption_pet_search")
     * @Template("animalline/adoption/pet/search.twig")
     */
    public function petSearch(Request $request)
    {
        return;
    }

    /**
     * ペット検索結果.
     *
     * @Route("/adoption/pet/search/result", name="adoption_pet_search_result")
     * @Template("animalline/adoption/pet/search_result.twig")
     */
    public function petSearchResult(PaginatorInterface $paginator, Request $request, ConservationPetsRepository $conservationPetsRepository): Response
    {
        $query = $conservationPetsRepository->findBy(
            ['release_status' => 1],
            ['release_date' => 'DESC']
        );
        $pets = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            4
        );

        return $this->render('animalline/adoption/pet/search_result.twig', ['pets' => $pets]);
    }

    /**
     * ペット詳細.
     *
     * @Route("/adoption/pet/detail/{id}", name="adoption_pet_detail", requirements={"id" = "\d+"})
     * @Template("animalline/adoption/pet/detail.twig")
     */
    public function petDetail(Request $request)
    {
        $id = $request->get('id');

        $conservationPet = $this->conservationPetsRepository->findOneBy(['id' => $id]);
        if (is_null($conservationPet)) {
            throw new HttpException\NotFoundHttpException();
        }

        $images = $conservationPet->getConservationPetImages();
        $pref = '';
        $conservation = $this->conservationsRepository->find($conservationPet->getConservationId());
        if ($conservation) $pref = $conservation->getConservationHousePref();

        return $this->render('animalline/adoption/pet/detail.twig', ['conservationPet' => $conservationPet, 'pref' => $pref, 'images' => $images]);
    }

    /**
     * よくある質問.
     *
     * @Route("/adoption/faq", name="adoption_faq")
     * @Template("animalline/adoption/faq.twig")
     */
    public function faq(Request $request)
    {
        return;
    }

    /**
     * サイト説明。初めての方へ.
     *
     * @Route("/adoption/readfirst", name="adoption_readfirst")
     * @Template("animalline/adoption/readfirst.twig")
     */
    public function readfirst(Request $request)
    {
        return;
    }

    /**
     * 最近見た子犬.
     *
     * @Route("/adoption/viewhist", name="adoption_viewhist")
     * @Template("animalline/adoption/viewhist.twig")
     */
    public function viewhist(Request $request)
    {
        return;
    }

    /**
     * お気に入り一覧.
     *
     * @Route("/adoption/favolite", name="adoption_favolite")
     * @Template("animalline/adoption/favolite.twig")
     */
    public function favolite(Request $request)
    {
        return;
    }

    /**
     * 保護団体リスト.
     *
     * @Route("/adoption/list", name="adoption_list")
     * @Template("animalline/adoption/list.twig")
     */
    public function list(Request $request)
    {
        return;
    }

    /**
     * マイページ.
     *
     * @Route("/adoption/mypage", name="adoption_mypage")
     * @Template("animalline/adoption/mypage.twig")
     */
    public function mypage(Request $request)
    {
        return;
    }
}
