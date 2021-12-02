<?php

namespace Customize\Controller\Breeder;

use Carbon\Carbon;
use Customize\Config\AnilineConf;
use Customize\Entity\BreederPetImage;
use Customize\Entity\BreederPets;
use Customize\Repository\BreederContactHeaderRepository;
use Customize\Repository\BreederEvaluationsRepository;
use Customize\Service\BreederQueryService;
use DateTime;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Customize\Form\Type\Breeder\BreederPetsType;
use Customize\Entity\BreederHouse;
use Customize\Repository\BreederPetsRepository;
use Customize\Repository\PetsFavoriteRepository;
use Eccube\Repository\Master\PrefRepository;
use Customize\Repository\BreederContactsRepository;
use Customize\Repository\SendoffReasonRepository;
use Customize\Repository\BreedersRepository;
use Customize\Repository\BreederHouseRepository;
use Customize\Repository\BreederExaminationInfoRepository;
use Customize\Repository\BreederPetImageRepository;
use Customize\Repository\BreederPetinfoTemplateRepository;
use Customize\Repository\DnaCheckStatusRepository;
use Customize\Service\DnaQueryService;
use Eccube\Repository\CustomerRepository;
use Eccube\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Customize\Repository\DnaCheckStatusHeaderRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BreederPetController extends AbstractController
{
    /**
     * @var BreederEvaluationsRepository
     */
    protected $breederEvaluationsRepository;

    /**
     * @var BreederContactHeaderRepository
     */
    protected $breederContactHeaderRepository;

    /**
     * @var BreederContactsRepository
     */
    protected $breederContactsRepository;

    /**
     * @var BreederQueryService
     */
    protected $breederQueryService;

    /**
     * @var PetsFavoriteRepository
     */
    protected $petsFavoriteRepository;

    /**
     * @var SendoffReasonRepository
     */
    protected $sendoffReasonRepository;

    /**
     * @var BreedersRepository
     */
    protected $breedersRepository;

    /**
     * @var BreederHouse
     */
    protected $breederHouseRepository;

    /**
     * @var PrefRepository
     */
    protected $prefRepository;

    /**
     * @var BreederPetsRepository
     */
    protected $breederPetsRepository;

    /**
     * @var BreederExaminationInfoRepository
     */
    protected $breederExaminationInfoRepository;

    /**
     * @var CustomerRepository
     */
    protected $customerRepository;

    /**
     * @var BreederPetImageRepository
     */
    protected $breederPetImageRepository;

    /**
     * @var DnaQueryService
     */
    protected $dnaQueryService;

    /**
     * @var DnaCheckStatusRepository
     */
    protected $dnaCheckStatusRepository;

    /**
     * @var DnaCheckStatusHeaderRepository
     */
    protected $dnaCheckStatusHeaderRepository;

    /**
     * @var BreederPetinfoTemplateRepository
     */
    protected $breederPetinfoTemplateRepository;

    /**
     * BreederController constructor.
     *
     * @param BreederContactsRepository $breederContactsRepository
     * @param BreederQueryService $breederQueryService
     * @param PetsFavoriteRepository $petsFavoriteRepository
     * @param SendoffReasonRepository $sendoffReasonRepository
     * @param BreedersRepository $breedersRepository
     * @param PrefRepository $prefRepository
     * @param BreederHouseRepository $breederHouseRepository
     * @param BreederPetsRepository $breederPetsRepository
     * @param BreederExaminationInfoRepository $breederExaminationInfoRepository
     * @param CustomerRepository $customerRepository
     * @param BreederContactHeaderRepository $breederContactHeaderRepository
     * @param BreederEvaluationsRepository $breederEvaluationsRepository
     * @param BreederPetImageRepository $breederPetImageRepository
     * @param DnaQueryService $dnaQueryService
     * @param DnaCheckStatusRepository $dnaCheckStatusRepository
     * @param DnaCheckStatusHeaderRepository $dnaCheckStatusHeaderRepository
     * @param BreederPetinfoTemplateRepository $breederPetinfoTemplateRepository
     */
    public function __construct(
        BreederContactsRepository        $breederContactsRepository,
        BreederQueryService              $breederQueryService,
        PetsFavoriteRepository           $petsFavoriteRepository,
        SendoffReasonRepository          $sendoffReasonRepository,
        BreedersRepository               $breedersRepository,
        PrefRepository                   $prefRepository,
        BreederHouseRepository           $breederHouseRepository,
        BreederPetsRepository            $breederPetsRepository,
        BreederExaminationInfoRepository $breederExaminationInfoRepository,
        CustomerRepository               $customerRepository,
        BreederContactHeaderRepository   $breederContactHeaderRepository,
        BreederEvaluationsRepository     $breederEvaluationsRepository,
        BreederPetImageRepository        $breederPetImageRepository,
        DnaQueryService                  $dnaQueryService,
        DnaCheckStatusRepository         $dnaCheckStatusRepository,
        DnaCheckStatusHeaderRepository   $dnaCheckStatusHeaderRepository,
        BreederPetinfoTemplateRepository $breederPetinfoTemplateRepository
    ) {
        $this->breederContactsRepository = $breederContactsRepository;
        $this->breederQueryService = $breederQueryService;
        $this->petsFavoriteRepository = $petsFavoriteRepository;
        $this->sendoffReasonRepository = $sendoffReasonRepository;
        $this->breedersRepository = $breedersRepository;
        $this->prefRepository = $prefRepository;
        $this->breederHouseRepository = $breederHouseRepository;
        $this->breederPetsRepository = $breederPetsRepository;
        $this->breederExaminationInfoRepository = $breederExaminationInfoRepository;
        $this->customerRepository = $customerRepository;
        $this->breederContactHeaderRepository = $breederContactHeaderRepository;
        $this->breederEvaluationsRepository = $breederEvaluationsRepository;
        $this->breederPetImageRepository = $breederPetImageRepository;
        $this->dnaQueryService = $dnaQueryService;
        $this->dnaCheckStatusRepository = $dnaCheckStatusRepository;
        $this->dnaCheckStatusHeaderRepository = $dnaCheckStatusHeaderRepository;
        $this->breederPetinfoTemplateRepository = $breederPetinfoTemplateRepository;
    }

    /**
     * 取扱ペット一覧TOP
     *
     * @Route("/breeder/member/pet_list", name="breeder_pet_list")
     * @Template("animalline/breeder/member/pet_list.twig")
     */
    public function breeder_pet_list(): ?Response
    {
        $pets = $this->breederPetsRepository->findBy(['Breeder' => $this->getUser()], ['update_date' => 'DESC']);

        return $this->render(
            'animalline/breeder/member/pet_list.twig',
            [
                'breeder' => $this->getUser(),
                'pets' => $pets,
            ]
        );
    }

    /**
     * ペット詳細
     *
     * @Route("/breeder/pet/detail/{id}", name="breeder_pet_detail", requirements={"id" = "\d+"})
     * @Template("animalline/breeder/pet/detail.twig")
     */
    public function petDetail(Request $request): ?Response
    {
        $isLoggedIn = (bool)$this->getUser();
        $id = $request->get('id');
        $isFavorite = false;
        $breederPet = $this->breederPetsRepository->find($id);
        $pedigree = $breederPet->getPedigree();
        $breederExamInfo = null;

        $isPedigree = $breederPet->getIsPedigree();
        if ($isPedigree == 1) {
            $breeder = $this->breedersRepository->find($breederPet->getBreeder());
            $breederExamInfo = $this->breederExaminationInfoRepository->findOneBy([
                'Breeder' => $breeder->getId(),
                'pet_type' => $breederPet->getPetKind(),
                'pedigree_organization' => 3
            ]);
            if (!$breederExamInfo) {
                $breederExamInfo = $this->breederExaminationInfoRepository->findOneBy([
                    'Breeder' => $breeder->getId(),
                    'pet_type' => $breederPet->getPetKind(),
                    'pedigree_organization' => [
                        1,
                        2
                    ]
                ]);
            }
        }

        $petKind = $breederPet->getPetKind();
        $favorite = $this->petsFavoriteRepository->findOneBy(['Customer' => $this->getUser(), 'pet_id' => $id]);
        if ($favorite) {
            $isFavorite = true;
        }

        $images = $this->breederPetImageRepository->findBy(
            [
                'BreederPets' => $breederPet,
                'image_type' => AnilineConf::PET_PHOTO_TYPE_IMAGE
            ]
        );
        $video = $this->breederPetImageRepository->findOneBy(
            [
                'BreederPets' => $breederPet,
                'image_type' => AnilineConf::PET_PHOTO_TYPE_VIDEO
            ]
        );
        
        $now = Carbon::now();
        $is56DaysOld = $now->diffInDays($breederPet->getPetBirthday());

        return $this->render(
            'animalline/breeder/pet/detail.twig',
            [
                'breederPet' => $breederPet,
                'petKind' => $petKind,
                'images' => $images,
                'video' => $video,
                'isFavorite' => $isFavorite,
                'isLoggedIn' => $isLoggedIn,
                'breederExamInfo' => $breederExamInfo,
                'pedigree' => $pedigree,
                'is56DaysOld' => $is56DaysOld
            ]
        );
    }

    /**
     * 新規ペット追加
     *
     * @Route("/breeder/member/pets/new/{barcode}", name="breeder_pets_new", methods={"GET","POST"}, requirements={"barcode" = "^\d{6}$"})
     */
    public function breeder_pets_new(Request $request, $barcode): Response
    {
        $dnaId = substr($barcode, 1);
        if (!$Dna = $this->dnaCheckStatusRepository->find($dnaId)) {
            throw new NotFoundHttpException();
        }
        $breederId = $Dna->getDnaHeader()->getRegisterId();

        $user = $this->getUser();
        $is_breeder = $user->getIsBreeder();
        if ($is_breeder == 0) {
            $breeder = $this->breedersRepository->find($breederId);

            return $this->render('animalline/breeder/member/examination_guidance.twig', [
                'breeder' => $breeder
            ]);
        }

        $breeder = $this->breedersRepository->find($user);
        if (!$breeder) {
            throw new NotFoundHttpException();
        }
        $petInfoTemplate = $this->breederPetinfoTemplateRepository->findOneBy([
            'Breeder' => $breeder
        ]);

        $image0 = $request->get('img0') ?? '';
        $image1 = $request->get('img1') ?? '';
        $image2 = $request->get('img2') ?? '';
        $image3 = $request->get('img3') ?? '';
        $image4 = $request->get('img4') ?? '';

        // if (!$petInfoTemplate) {
        //     throw new NotFoundHttpException();
        // }
        $breederPet = new BreederPets();
        $form = $this->createForm(BreederPetsType::class, $breederPet, [
            'customer' => $this->getUser(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($request->get('breeder_pets')['is_pedigree'] == 0 || $request->get('breeder_pets')['pedigree_code']) {
                $breederPet->setPedigreeCode('0');
            }
            $entityManager = $this->getDoctrine()->getManager();
            $breeder = $this->breedersRepository->find($breederId);
            $breederPet->setBreeder($breeder);
            $breederPet->setDnaCheckResult(0);
            $entityManager->persist($breederPet);
            $entityManager->flush();
            $petId = $breederPet->getId();
            $img0 = $this->setImageSrc($request->get('img0'), $petId);
            $img1 = $this->setImageSrc($request->get('img1'), $petId);
            $img2 = $this->setImageSrc($request->get('img2'), $petId);
            $img3 = $this->setImageSrc($request->get('img3'), $petId);
            $img4 = $this->setImageSrc($request->get('img4'), $petId);

            $petImage0 = (new BreederPetImage())
                ->setImageType(AnilineConf::PET_PHOTO_TYPE_IMAGE)->setImageUri($img0)->setSortOrder(1)
                ->setBreederPet($breederPet);
            $petImage1 = (new BreederPetImage())
                ->setImageType(AnilineConf::PET_PHOTO_TYPE_IMAGE)->setImageUri($img1)->setSortOrder(2)
                ->setBreederPet($breederPet);
            $petImage2 = (new BreederPetImage())
                ->setImageType(AnilineConf::PET_PHOTO_TYPE_IMAGE)->setImageUri($img2)->setSortOrder(3)
                ->setBreederPet($breederPet);
            $petImage3 = (new BreederPetImage())
                ->setImageType(AnilineConf::PET_PHOTO_TYPE_IMAGE)->setImageUri($img3)->setSortOrder(4)
                ->setBreederPet($breederPet);
            $petImage4 = (new BreederPetImage())
                ->setImageType(AnilineConf::PET_PHOTO_TYPE_IMAGE)->setImageUri($img4)->setSortOrder(5)
                ->setBreederPet($breederPet);
            $breederPet
                ->addBreederPetImage($petImage0)
                ->addBreederPetImage($petImage1)
                ->addBreederPetImage($petImage2)
                ->addBreederPetImage($petImage3)
                ->addBreederPetImage($petImage4)
                ->setThumbnailPath($img0)
                ->setPetCode($barcode);

            // update dna check status
            $Dna->setPetId($breederPet->getId())
                ->setCheckStatus(AnilineConf::ANILINE_DNA_CHECK_STATUS_PET_REGISTERED)
                ->setKitPetRegisterDate(new DateTime);

            $entityManager->persist($petImage0);
            $entityManager->persist($petImage1);
            $entityManager->persist($petImage2);
            $entityManager->persist($petImage3);
            $entityManager->persist($petImage4);
            $entityManager->persist($breederPet);
            $entityManager->persist($Dna);
            $entityManager->flush();

            return $this->redirectToRoute('breeder_newpet_complete');
            //return $this->render('animalline/breeder/member/pets/notification.twig');
        }

        return $this->render('animalline/breeder/member/pets/new.twig', [
            'form' => $form->createView(),
            'petInfoTemplate' => $petInfoTemplate,
            'image0' => $image0,
            'image1' => $image1,
            'image2' => $image2,
            'image3' => $image3,
            'image4' => $image4
        ]);
    }

    /**
     *
     * 新規ペット追加完了メッセージ
     *
     * @Route("/breeder/member/pets/new_complete", name="breeder_newpet_complete", methods={"GET","POST"})
     * @Template("animalline/breeder/member/pets/notification.twig")
     */
    public function breeder_pets_new_complete(): array
    {
        return [];
    }

    /**
     * ペット情報編集
     *
     * @Route("/breeder/member/pets/edit/{id}", name="breeder_pets_edit", methods={"GET","POST"})
     * @Template("animalline/breeder/member/pets/edit.twig")
     */
    public function breeder_pets_edit(Request $request, BreederPets $breederPet)
    {
        $user = $this->getUser();
        $breeder = $this->breedersRepository->find($user);
        if (!$breeder) {
            throw new NotFoundHttpException();
        }
        $petInfoTemplate = $this->breederPetinfoTemplateRepository->findOneBy([
            'Breeder' => $breeder
        ]);
        if (!$petInfoTemplate) {
            throw new NotFoundHttpException();
        }

        $form = $this->createForm(BreederPetsType::class, $breederPet, [
            'customer' => $this->getUser(),
        ]);
        $breederPetImages = $this->breederPetImageRepository->findBy(
            ['BreederPets' => $breederPet, 'image_type' => AnilineConf::PET_PHOTO_TYPE_IMAGE],
            ['sort_order' => 'ASC']
        );

        $image0 = $request->get('img0') ?? '';
        $image1 = $request->get('img1') ?? '';
        $image2 = $request->get('img2') ?? '';
        $image3 = $request->get('img3') ?? '';
        $image4 = $request->get('img4') ?? '';

        $request->request->set('thumbnail_path', $image0 ?: ($breederPet->getThumbnailPath() ? '/' . AnilineConf::ANILINE_IMAGE_URL_BASE . $breederPet->getThumbnailPath() : ''));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($request->get('breeder_pets')['is_pedigree'] == 0 || $request->get('breeder_pets')['pedigree_code']) {
                $breederPet->setPedigreeCode('0');
            }
            $petId = $breederPet->getId();
            $img0 = $this->setImageSrc($request->get('img0'), $petId);
            $img1 = $this->setImageSrc($request->get('img1'), $petId);
            $img2 = $this->setImageSrc($request->get('img2'), $petId);
            $img3 = $this->setImageSrc($request->get('img3'), $petId);
            $img4 = $this->setImageSrc($request->get('img4'), $petId);
            $entityManager = $this->getDoctrine()->getManager();
            $breederPet->setThumbnailPath($img0);
            $entityManager->persist($breederPet);
            foreach ($breederPetImages as $key => $image) {
                $image->setImageUri(${'img' . $key});
                $entityManager->persist($image);
            }
            $entityManager->flush();

            return $this->redirectToRoute('breeder_pet_list');
        }

        $petImages = [];
        foreach ($breederPetImages as $key => $image) {
            if ($form->isSubmitted()) {
                $petImages[$key] = [
                    'image_uri' => $request->get('img' . $key),
                    'sort_order' => $image->getSortOrder()
                ];
            } else {
                $petImages[$key] = [
                    'image_uri' => $image->getImageUri() ? '/' . AnilineConf::ANILINE_IMAGE_URL_BASE . $image->getImageUri() : '',
                    'sort_order' => $image->getSortOrder()
                ];
            }
        }

        return [
            'breeder_pet' => $breederPet,
            'pet_mages' => $petImages,
            'thumbnailPath' => $request->get('thumbnail_path'),
            'form' => $form->createView(),
            'petInfoTemplate' => $petInfoTemplate,
            'image0' => $image0,
            'image1' => $image1,
            'image2' => $image2,
            'image3' => $image3,
            'image4' => $image4
        ];
    }

    /**
     * ペットの状態を変更する
     *
     * @Route("/breeder/member/pets/edit/{id}/change_status", name="breeder_pets_edit_change_status", methods={"GET"})
     */
    public function breeder_pets_change_status(Request $request, BreederPets $breederPet)
    {
        $curStatus = $breederPet->getIsActive();
        if ($curStatus === AnilineConf::IS_ACTIVE_PRIVATE) {
            $breederPet->setIsActive(AnilineConf::IS_ACTIVE_PUBLIC);
            $breederPet->setReleaseDate(Carbon::now());
        } elseif ($curStatus === AnilineConf::IS_ACTIVE_PUBLIC) {
            $breederPet->setIsActive(AnilineConf::IS_ACTIVE_PRIVATE);
            $breederPet->setReleaseDate(null);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($breederPet);
        $em->flush();

        return $this->redirectToRoute('breeder_pets_edit', ['id' => $breederPet->getId()]);
    }

    /**
     * Copy image and retrieve new url of the copy
     *
     * @param string $imageUrl
     * @param int $petId
     * @return string
     */
    private function setImageSrc(string $imageUrl, int $petId): string
    {
        if (empty($imageUrl)) {
            return '';
        }

        $imageUrl = ltrim($imageUrl, '/');
        $resource = str_replace(
            AnilineConf::ANILINE_IMAGE_URL_BASE,
            '',
            $imageUrl
        );
        $arr = explode('/', ltrim($resource, '/'));
        if ($arr[0] === 'breeder') {
            return $resource;
        }

        $imageName = str_replace(
            AnilineConf::ANILINE_IMAGE_URL_BASE . '/tmp/',
            '',
            $imageUrl
        );
        $subUrl = AnilineConf::ANILINE_IMAGE_URL_BASE . '/breeder/' . $petId . '/';
        if (!file_exists($subUrl)) {
            mkdir($subUrl, 0777, 'R');
        }

        copy($imageUrl, $subUrl . $imageName);
        return '/breeder/' . $petId . '/' . $imageName;
    }

    /**
     *
     * ペット登録一覧
     *
     * @Route("/breeder/member/pet_regist_list", name="breeder_pet_regist_list")
     * @Template("animalline/breeder/member/pets/regist_list.twig")
     */
    public function pet_regist_list(Request $request, PaginatorInterface $paginator): array
    {
        $codes = [];
        $dnaCheckStatusHeaders = $this->dnaCheckStatusHeaderRepository->findBy(['register_id' => $this->getUser()->getId()]);
        $DnaCheckStatus = $this->dnaCheckStatusRepository->createQueryBuilder('dcs')
            ->where('dcs.DnaHeader IN(:arr)')
            ->andWhere('dcs.site_type = :siteType')
            ->andWhere('dcs.check_status = :checkStatus')
            ->setParameter('siteType', AnilineConf::ANILINE_SITE_TYPE_BREEDER)
            ->setParameter('checkStatus', AnilineConf::ANILINE_DNA_CHECK_STATUS_SHIPPING)
            ->setParameter('arr', $dnaCheckStatusHeaders)
            ->addOrderBy('dcs.update_date', 'DESC')
            ->getQuery()->getResult();
        foreach ($DnaCheckStatus as $dnaCheckStatus) {
            $codes[] = '1' . str_pad($dnaCheckStatus->getId(), 5, '0', STR_PAD_LEFT);
        }
        $barCodes = $paginator->paginate(
            $codes,
            $request->query->getInt('page', 1),
            AnilineConf::ANILINE_NUMBER_ITEM_PER_PAGE
        );
        return compact('barCodes');
    }

    /**
     *
     * @Route("/b", name="b")
     */
    public function b()
    {
        return $this->redirectToRoute('breeder_pet_regist_list');
    }
}
