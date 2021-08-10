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


use Customize\Config\AnilineConf;
use Customize\Repository\ConservationPetsRepository;
use Eccube\Controller\AbstractController;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception as HttpException;
use Symfony\Component\Routing\Annotation\Route;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Customize\Form\Type\ConservationContactType;


class AdoptionController extends AbstractController
{
    /**
     * @var ConservationPetsRepository
     */
    protected $conservationPetsRepository;


    /**
     * AdoptionController constructor.
     *
     * @param
     */
    public function __construct(
        ConservationPetsRepository $conservationPetsRepository
    )
    {
        $this->conservationPetsRepository = $conservationPetsRepository;
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
            AnilineConf::ANILINE_NUMBER_ITEM_PER_PAGE
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

        $conservationPet = $this->conservationPetsRepository->find($id);
        if (!$conservationPet) {
            throw new HttpException\NotFoundHttpException();
        }

        $images = $conservationPet->getConservationPetImages();

        return $this->render(
            'animalline/adoption/pet/detail.twig',
            ['conservationPet' => $conservationPet, 'images' => $images]
        );
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
     * @Route("/adoption/configration/mypage", name="adoption_mypage")
     * @Template("animalline/adoption/mypage.twig")
     */
    public function mypage(Request $request)
    {
        return;
    }

    /**
     * お問い合わせ.
     *
     * @Route("/adoption/member/contact/{pet_id}", name="adpotion_contact", requirements={"pet_id" = "\d+"})
     * @Template("/animalline/adoption/contact.twig")
     */
    public function contact(Request $request)
    {

        $builder = $this->formFactory->createBuilder(ConservationContactType::class);

        // if ($this->isGranted('ROLE_ADOPTION_USER')) {
        //     /** @var Customer $user */
        //     $user = $this->getUser();
        //     $builder->setData(
        //         [
        //             'name01' => $user->getName01(),
        //             'name02' => $user->getName02(),
        //             'kana01' => $user->getKana01(),
        //             'kana02' => $user->getKana02(),
        //             'postal_code' => $user->getPostalCode(),
        //             'pref' => $user->getPref(),
        //             'addr01' => $user->getAddr01(),
        //             'addr02' => $user->getAddr02(),
        //             'phone_number' => $user->getPhoneNumber(),
        //             'email' => $user->getEmail(),
        //         ]
        //     );
        // }

        // FRONT_CONTACT_INDEX_INITIALIZE
        $event = new EventArgs(
            [
                'builder' => $builder,
            ],
            $request
        );
        $this->eventDispatcher->dispatch(EccubeEvents::FRONT_CONTACT_INDEX_INITIALIZE, $event);

        $form = $builder->getForm();
        $form->handleRequest($request);      

        return [
            'form' => $form->createView(),
        ];
    }

}
