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

namespace Customize\Controller\Admin\Pet;

use Customize\Repository\BreederContactHeaderRepository;
use Customize\Repository\BreederContactsRepository;
use Customize\Repository\BreedsRepository;
use Customize\Repository\ConservationContactHeaderRepository;
use Customize\Repository\ConservationContactsRepository;
use Customize\Service\BreederQueryService;
use Eccube\Controller\AbstractController;
use Eccube\Repository\CustomerRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Customize\Config\AnilineConf;
use Customize\Entity\BreederExaminationInfo;
use Customize\Entity\BreederPets;
use Customize\Form\Type\Admin\BreederPetsType;
use Customize\Repository\BreederPetImageRepository;
use Knp\Component\Pager\PaginatorInterface;
use Customize\Repository\BreederPetsRepository;
use Customize\Repository\ConservationPetsRepository;
use Customize\Repository\DnaCheckStatusRepository;
use Customize\Repository\BreederEvaluationsRepository;
use Customize\Service\MailService;
use DateTime;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Doctrine\ORM\EntityManagerInterface;

class PetController extends AbstractController
{
    /**
     * @var BreedsRepository
     */
    protected $breedsRepository;

    /**
     * @var BreederPetImageRepository
     */
    protected $breederPetImageRepository;

    /**
     * @var BreederQueryService
     */
    protected $breederQueryService;

    /**
     * @var BreederPetsRepository
     */
    protected $breederPetsRepository;

    /**
     * @var ConservationPetsRepository
     */
    protected $conservationPetsRepository;

    /**
     * @var ConservationContactHeaderRepository
     */
    protected $conservationContactHeaderRepository;

    /**
     * @var BreederContactHeaderRepository
     */
    protected $breederContactHeaderRepository;

    /**
     * @var BreederContactsRepository
     */
    protected $breederContactsRepository;

    /**
     * @var ConservationContactsRepository
     */
    protected $conservationContactsRepository;

    /**
     * @var CustomerRepository
     */
    protected $customerRepository;

    /**
     * @var BreederEvaluationsRepository
     */
    protected $breederEvaluationsRepository;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * PetController constructor.
     * @param BreedsRepository $breedsRepository
     * @param BreederPetImageRepository $breederPetImageRepository
     * @param BreederQueryService $breederQueryService
     * @param BreederPetsRepository $breederPetsRepository
     * @param ConservationPetsRepository $conservationPetsRepository
     * @param ConservationContactHeaderRepository $conservationContactHeaderRepository
     * @param BreederContactHeaderRepository $breederContactHeaderRepository
     * @param CustomerRepository $customerRepository
     * @param BreederContactsRepository $breederContactsRepository
     * @param ConservationContactsRepository $conservationContactsRepository
     * @param BreederEvaluationsRepository $breederEvaluationsRepository
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        BreedsRepository          $breedsRepository,
        BreederPetImageRepository $breederPetImageRepository,
        BreederQueryService       $breederQueryService,
        BreederPetsRepository       $breederPetsRepository,
        ConservationPetsRepository $conservationPetsRepository,
        ConservationContactHeaderRepository $conservationContactHeaderRepository,
        BreederContactHeaderRepository $breederContactHeaderRepository,
        CustomerRepository $customerRepository,
        BreederContactsRepository $breederContactsRepository,
        ConservationContactsRepository $conservationContactsRepository,
        BreederEvaluationsRepository $breederEvaluationsRepository
    ) {
        $this->breedsRepository = $breedsRepository;
        $this->breederQueryService = $breederQueryService;
        $this->breederPetImageRepository = $breederPetImageRepository;
        $this->breederPetsRepository = $breederPetsRepository;
        $this->conservationPetsRepository = $conservationPetsRepository;
        $this->conservationContactHeaderRepository = $conservationContactHeaderRepository;
        $this->breederContactHeaderRepository = $breederContactHeaderRepository;
        $this->customerRepository = $customerRepository;
        $this->breederContactsRepository = $breederContactsRepository;
        $this->conservationContactsRepository = $conservationContactsRepository;
        $this->breederEvaluationsRepository = $breederEvaluationsRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * ペット情報管理
     *
     * @Route("/%eccube_admin_route%/pet", name="admin_pet_all")

     * @Template("@admin/Pet/all.twig")
     */
    public function pet_all(PaginatorInterface $paginator, Request $request)
    {
        $request = $request->query->all();

        // $breeds = $this->breedsRepository->findBy([], ['breeds_name' => 'ASC']);
        $breeds = $this->breedsRepository->findBy([], ['sort_order' => 'ASC']);
        $order = [];
        $order['field'] = array_key_exists('field', $request) ? $request['field'] : 'create_date';
        $order['direction'] = array_key_exists('direction', $request) ? $request['direction'] : 'DESC';
        $criteria = [];
        $criteria['pet_kind'] = array_key_exists('pet_kind', $request) ? $request['pet_kind'] : '';
        $criteria['breed_type'] = array_key_exists('breed_type', $request) ? $request['breed_type'] : '';
        $criteria['public_status'] = array_key_exists('public_status', $request) ? $request['public_status'] : '';
        $criteria['holder_name'] = array_key_exists('holder_name', $request) ? $request['holder_name'] : '';
        if (array_key_exists('create_date_start', $request)) {
            $criteria['create_date_start'] = $request['create_date_start'];
        }
        if (array_key_exists('create_date_end', $request)) {
            $criteria['create_date_end'] = $request['create_date_end'];
        }
        if (array_key_exists('update_date_start', $request)) {
            $criteria['update_date_start'] = $request['update_date_start'];
        }
        if (array_key_exists('update_date_end', $request)) {
            $criteria['update_date_end'] = $request['update_date_end'];
        }

        $siteKind = $request['site_kind'] ?? '';
        $breederPets = [];
        $conservationPets = [];
        if ($siteKind == AnilineConf::ANILINE_SITE_TYPE_ADOPTION) {
            $conservationPets = $this->conservationPetsRepository->filterConservationPetsAdmin($criteria, $order);
        } elseif ($siteKind == AnilineConf::ANILINE_SITE_TYPE_BREEDER) {
            $breederPets = $this->breederPetsRepository->filterBreederPetsAdmin($criteria, $order);
        }

        $breederPets = $paginator->paginate(
            $breederPets,
            array_key_exists('page', $request) ? $request['page'] : 1,
            AnilineConf::ANILINE_NUMBER_ITEM_PER_PAGE
        );
        $conservationPets = $paginator->paginate(
            $conservationPets,
            array_key_exists('page', $request) ? $request['page'] : 1,
            AnilineConf::ANILINE_NUMBER_ITEM_PER_PAGE
        );

        foreach ($breederPets as $index => $breederPet) {
            // Get last contract status
            $contractHeader = $this->breederContactHeaderRepository->findLastContractHeaderByPet(
                $breederPet['bp_id']
            );
            if (empty($contractHeader)) {
                $breederPet['contract_status'] = null;
                $breederPet['have_messages'] = false;
            } else {
                $breederPet['contract_status'] = $contractHeader[0]['contract_status'];
                $breederPet['have_messages'] = true;
            }

            $breederPets[$index] = $breederPet;
        }

        foreach ($conservationPets as $index => $conservationPet) {
            // Get last contract status
            $contractHeader = $this->conservationContactHeaderRepository->findLastContractHeaderByPet(
                $conservationPet['cp_id']
            );
            if (empty($contractHeader)) {
                $conservationPet['contract_status'] = null;
                $conservationPet['have_messages'] = false;
            } else {
                $conservationPet['contract_status'] = $contractHeader[0]['contract_status'];
                $conservationPet['have_messages'] = true;
            }

            $conservationPets[$index] = $conservationPet;
        }

        $direction = 'ASC';
        if (array_key_exists('direction', $request)) {
            $direction = $request['direction'] == 'ASC' ? 'DESC' : 'ASC';
        }

        return [
            'breeds' => $breeds,
            'direction' => $direction,
            'breederPets' => $breederPets,
            'conservationPets' => $conservationPets,
        ];
    }

    /**
     * ペット毎お問い合わせ一覧
     *
     * @Route("/%eccube_admin_route%/pet/all_message/{pet_id}/{site_kind}", name="admin_pet_all_message")

     * @Template("@admin/Pet/all_message.twig")
     */
    public function all_message(PaginatorInterface $paginator, Request $request)
    {
        $site_kind = $request->get('site_kind');
        $pet_id = $request->get('pet_id');

        if ($site_kind == AnilineConf::ANILINE_SITE_TYPE_BREEDER) {
            $records = $this->breederContactHeaderRepository->findBy([
                'Pet' => $pet_id
            ]);
        } else {
            $records = $this->conservationContactHeaderRepository->findBy([
                'Pet' => $pet_id
            ]);
        }

        $request = $request->query->all();
        $contacts = $paginator->paginate(
            $records,
            array_key_exists('page', $request) ? $request['page'] : 1,
            AnilineConf::ANILINE_NUMBER_ITEM_PER_PAGE
        );

        return [
            'contacts' => $contacts,
            'site_kind' => $site_kind
        ];
    }

    /**
     * お問い合わせ内容確認
     *
     * @Route("/%eccube_admin_route%/pet/message/{id}/{site_kind}", name="admin_pet_message")

     * @Template("@admin/Pet/message.twig")
     */
    public function message(PaginatorInterface $paginator, Request $request)
    {
        $breeder = null;
        $conservation = null;
        $breederContacts = [];
        $conservationContacts = [];
        $contactId = $request->get('id');
        if ($request->get('site_kind') == AnilineConf::SITE_CATEGORY_BREEDER) {
            $contact = $this->breederContactHeaderRepository->find($contactId);
            $breeder = $this->customerRepository->find($contact->getBreeder());
            $breederContacts = $this->breederContactsRepository->findBy(['BreederContactHeader' => $contact]);
        } else {
            $contact = $this->conservationContactHeaderRepository->find($contactId);
            $conservation = $this->customerRepository->find($contact->getConservation());
            $conservationContacts = $this->conservationContactsRepository->findBy(['ConservationContactHeader' => $contact]);
        }
        $customer = $this->customerRepository->find($contact->getCustomer());
        $breederContacts = $paginator->paginate(
            $breederContacts,
            $request->query->getInt('page', 1),
            AnilineConf::ANILINE_NUMBER_ITEM_PER_PAGE
        );
        $conservationContacts = $paginator->paginate(
            $conservationContacts,
            $request->query->getInt('page', 1),
            AnilineConf::ANILINE_NUMBER_ITEM_PER_PAGE
        );
        return compact([
            'contact',
            'breeder',
            'conservation',
            'customer',
            'breederContacts',
            'conservationContacts',
        ]);
    }

    /**
     * 審査結果登録ブリーダー管理
     *
     * @Route("/%eccube_admin_route%/pet/{id}/public_status/change", name="admin_pet_change_public_status", requirements={"id" = "\d+"})
     * @Template("@admin/Pet/public_status.twig")
     */
    public function change_public_status(Request $request, MailService $mailService)
    {
        $id = $request->get('id');
        $siteKind = $request->get('site_kind');
        $Pet = $siteKind == AnilineConf::ANILINE_SITE_TYPE_BREEDER ?
            $this->breederPetsRepository->find($id) :
            $this->conservationPetsRepository->find($id);
        if (!$Pet) throw new NotFoundHttpException();

        $holderId = $siteKind == AnilineConf::ANILINE_SITE_TYPE_BREEDER ?
            $Pet->getBreeder()->getId() :
            $Pet->getConservation()->getId();
        $Customer = $this->customerRepository->find($holderId);
        if (!$Customer) throw new NotFoundHttpException();

        $comment = $request->get('examination_result_comment');

        $breeds = $this->breedsRepository->find($Pet->getBreedsType());

        if ($siteKind == AnilineConf::ANILINE_SITE_TYPE_BREEDER) {
            switch($Pet->getBandColor()){
                case AnilineConf::ANILINE_BAND_COLOR_RED:
                    $color = "赤";
                    break;
                case AnilineConf::ANILINE_BAND_COLOR_BLUE:
                    $color = "青";
                    break;
                case AnilineConf::ANILINE_BAND_COLOR_GREEN:
                    $color = "緑";
                    break;
                case AnilineConf::ANILINE_BAND_COLOR_YELLOW:
                    $color = "黄色";
                    break;
                case AnilineConf::ANILINE_BAND_COLOR_PINK:
                    $color = "ピンク";
                    break;
                case AnilineConf::ANILINE_BAND_COLOR_ORANGE:
                    $color = "オレンジ";
                    break;
                default:
                    $color = "";
            }
        }

        $data = [
            'name' => "{$Customer->getName01()} {$Customer->getName02()}",
            'examination_comment' => "<span id='ex-comment'>{$comment}</span>",
            'pet_id' =>  $Pet->getId(),
            'pet_code' =>  $siteKind == AnilineConf::ANILINE_SITE_TYPE_BREEDER ? $Pet->getPetCode() : '',
            'pet_breeds' =>  $breeds->getBreedsName(),
            'pet_birthday' =>  $Pet->getPetBirthday() ? $Pet->getPetBirthday()->format("Y年m月d日") : '',
            'pet_bandcolor' =>  $siteKind == AnilineConf::ANILINE_SITE_TYPE_BREEDER ? $color : '',
            'site_kind' => $siteKind,
        ];

        if ($request->isMethod('POST')) {
            $result = (int)$request->get('examination_result');
            $Pet->setIsActive($result);
            if ($result === AnilineConf::IS_ACTIVE_PUBLIC) $Pet->setReleaseDate(new DateTime);
            $entityManager = $this->entityManager;
            $entityManager->persist($Pet);
            $entityManager->flush();

            $data['examination_comment'] = $comment;
            if ($result === AnilineConf::IS_ACTIVE_PUBLIC) {
                $mailService->sendPetPublicOk($Customer, $data);
            } else {
                $mailService->sendPetPublicNg($Customer, $data);
            }

            $this->addSuccess('公開ステータスを変更しました。', 'admin');
            return $this->redirectToRoute('admin_pet_change_public_status', ['id' => $id, 'site_kind' => $siteKind]);
        }

        $isActive = $Pet->getIsActive();
        return compact(
            'isActive',
            'data'
        );
    }


    /**
     * 評価確認
     *
     * @Route("/%eccube_admin_route%/pet/evaluation/{pet_id}", name="admin_pet_evaluation")

     * @Template("@admin/Pet/evaluation.twig")
     */
    public function evaluation(Request $request)
    {
        $pet_id = $request->get('pet_id');
        $evaluation = $this->breederEvaluationsRepository->findOneBy(['Pet' => $pet_id]);

        return [
            'evaluation' => $evaluation
        ];
    }
}
