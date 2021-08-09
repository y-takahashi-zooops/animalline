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

use Eccube\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Customize\Repository\ConservationPetsRepository;
use Customize\Repository\ConservationsRepository;
use Customize\Repository\BreedsRepository;
use Customize\Repository\CoatColorsRepository;
use Symfony\Component\HttpKernel\Exception as HttpException;

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
    ) {
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
    public function petSearchResult(Request $request)
    {
        return;
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

        $pref = '';
        $conservation = $this->conservationsRepository->find($conservationPet->getBreederId());
        if($conservation) $pref = $conservation->getConservationHousePref();

        $name = '';
        $breed = $this->breedsRepository->find($conservationPet->getBreedsType());
        if ($breed) $name = $breed->getBreedsName();

        $coatColor = $this->coatColorsRepository->findOneBy(['id' => $conservationPet->getCoatColor()])->getCoatColorName();

        //var_dump($conservationPet->getCoatColor2()->getCoatColorName());

        return $this->render('animalline/adoption/pet/detail.twig', ['conservationPet' => $conservationPet, 'coatColor' => $coatColor, 'pref' => $pref, 'name' => $name]);
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
