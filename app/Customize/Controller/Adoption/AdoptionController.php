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
use Customize\Entity\ConservationContacts;
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
use Customize\Repository\ConservationContactsRepository;
use DateTime;

class AdoptionController extends AbstractController
{
    /**
     * @var ConservationPetsRepository
     */
    protected $conservationPetsRepository;

    /**
     * @var ConservationContactsRepository
     */
    protected $conservationContactsRepository;


    /**
     * AdoptionController constructor.
     *
     * @param
     */
    public function __construct(
        ConservationPetsRepository $conservationPetsRepository,
        ConservationContactsRepository $conservationContactsRepository
    ) {
        $this->conservationPetsRepository = $conservationPetsRepository;
        $this->conservationContactsRepository = $conservationContactsRepository;
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
     * 保護団体管理ページTOP
     *
     * @Route("/adoption/configration", name="adoption_configration")
     * @Template("animalline/adoption/configration/index.twig")
     */
    public function adoption_configration(Request $request)
    {
        $rootMessages = $this->conservationContactsRepository->findBy(
            ['parent_message_id' => 0],
            ['is_response' => 'ASC', 'send_date' => 'DESC']
        );

        return $this->render(
            'animalline/adoption/configration/index.twig',
            ['rootMessages' => $rootMessages]
        );
    }
    /**
     * 保護団体管理ページ - 取引メッセージ履歴
     *
     * @Route("/adoption/configration/message/{contact_id}", name="adoption_configration_messages", requirements={"contact_id" = "\d+"})
     * @Template("animalline/adoption/configration/message.twig")
     */
    public function adoption_configration_message(Request $request, $contact_id)
    {
        if ($this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $rootMessage = $this->conservationContactsRepository->find($contact_id);
            if (!$rootMessage) {
                throw new HttpException\NotFoundHttpException();
            }

            $description = $request->get('contact_description');

            $conservationContact = new ConservationContacts();
            $form = $this->createFormBuilder($conservationContact)->getForm();
            $form->handleRequest($request);

            if ($form->isSubmitted()) {
                $conservationContact->setConservation($this->getUser())
                    ->setMessageFrom(2)
                    ->setPet($rootMessage->getPet())
                    ->setContactType(3)
                    ->setContactDescription($description)
                    ->setParentMessageId($contact_id)
                    ->setSendDate(new DateTime())
                    ->setIsResponse(1)
                    ->setContractStatus(0)
                    ->setReason(0);
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($conservationContact);
                $entityManager->flush();
            }
            $messages = $this->conservationContactsRepository->findBy(
                ['parent_message_id' => $contact_id],
                ['send_date' => 'ASC']
            );
            return $this->render('animalline/adoption/configration/message.twig', [
                'rootMessage' => $rootMessage,
                'messages' => $messages,
                'form' => $form->createView()
            ]);
        }
    }

    /**
     * 保護団体用ユーザーページ
     *
     * @Route("/adoption/member/", name="adoption_mypage")
     * @Template("animalline/adoption/member/index.twig")
     */
    public function adoption_mypage(Request $request)
    {
        return;
    }

    /**
     * 保護団体用ユーザーページ - 取引メッセージ履歴
     *
     * @Route("/adoption/member/message", name="adoption_mypage_messages")
     * @Template("animalline/adoption/member/message.twig")
     */
    public function adoption_message(Request $request)
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
