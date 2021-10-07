<?php

namespace Customize\Controller\Breeder;

use Customize\Config\AnilineConf;
use Customize\Entity\BreederPetImage;
use Customize\Entity\BreederPets;
use Customize\Repository\BreederContactHeaderRepository;
use Customize\Repository\BreederEvaluationsRepository;
use Customize\Service\BreederQueryService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Customize\Form\Type\BreederPetsType;
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
use Customize\Repository\DnaCheckStatusRepository;
use Customize\Service\DnaQueryService;
use Eccube\Repository\CustomerRepository;
use Eccube\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Customize\Repository\DnaCheckStatusHeaderRepository;
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
     * @var DnaQueryService;
     */
    protected $dnaQueryService;

    /**
     * @var DnaCheckStatusRepository;
     */
    protected $dnaCheckStatusRepository;

    /**
     * @var DnaCheckStatusHeaderRepository;
     */
    protected $dnaCheckStatusHeaderRepository;

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
     * @param BreederPetImageRepository $breederPetImageRepository
     * @param DnaQueryService $dnaQueryService
     * @param DnaCheckStatusRepository $dnaCheckStatusRepository
     * @param DnaCheckStatusHeaderRepository $dnaCheckStatusHeaderRepository
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
        DnaCheckStatusHeaderRepository   $dnaCheckStatusHeaderRepository
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
    }

    /**
     * 取扱ペット一覧TOP
     *
     * @Route("/breeder/member/pet_list", name="breeder_pet_list")
     * @Template("animalline/breeder/member/pet_list.twig")
     */
    public function breeder_pet_list(Request $request)
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

        $breederPet = new BreederPets();
        $form = $this->createForm(BreederPetsType::class, $breederPet, [
            'customer' => $this->getUser(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
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
                ->setBreederPetId($breederPet);
            $petImage1 = (new BreederPetImage())
                ->setImageType(AnilineConf::PET_PHOTO_TYPE_IMAGE)->setImageUri($img1)->setSortOrder(2)
                ->setBreederPetId($breederPet);
            $petImage2 = (new BreederPetImage())
                ->setImageType(AnilineConf::PET_PHOTO_TYPE_IMAGE)->setImageUri($img2)->setSortOrder(3)
                ->setBreederPetId($breederPet);
            $petImage3 = (new BreederPetImage())
                ->setImageType(AnilineConf::PET_PHOTO_TYPE_IMAGE)->setImageUri($img3)->setSortOrder(4)
                ->setBreederPetId($breederPet);
            $petImage4 = (new BreederPetImage())
                ->setImageType(AnilineConf::PET_PHOTO_TYPE_IMAGE)->setImageUri($img4)->setSortOrder(5)
                ->setBreederPetId($breederPet);
            $breederPet
                ->addBreederPetImage($petImage0)
                ->addBreederPetImage($petImage1)
                ->addBreederPetImage($petImage2)
                ->addBreederPetImage($petImage3)
                ->addBreederPetImage($petImage4)
                ->setThumbnailPath($img0);

            // update dna check status
            $Dna->setPetId($breederPet->getId())
                ->setCheckStatus(AnilineConf::ANILINE_DNA_CHECK_STATUS_PET_REGISTERED);

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
            'form' => $form->createView()
        ]);
    }

    /**
     *
     * 新規ペット追加完了メッセージ
     *
     * @Route("/breeder/member/pets/new_complete", name="breeder_newpet_complete", methods={"GET","POST"})
     * @Template("animalline/breeder/member/pets/notification.twig")
     */
    public function breeder_pets_new_complete()
    {
        return [];
    }

    /**
     * ペット情報編集
     *
     * @Route("/breeder/member/pets/edit/{id}", name="breeder_pets_edit", methods={"GET","POST"})
     */
    public function breeder_pets_edit(Request $request, BreederPets $breederPet): Response
    {
        $form = $this->createForm(BreederPetsType::class, $breederPet, [
            'customer' => $this->getUser(),
        ]);
        $breederPetImages = $this->breederPetImageRepository->findBy(
            ['BreederPets' => $breederPet, 'image_type' => AnilineConf::PET_PHOTO_TYPE_IMAGE],
            ['sort_order' => 'ASC']
        );
        $request->request->set('thumbnail_path', $breederPet->getThumbnailPath());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
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
        foreach ($breederPetImages as $image) {
            $petImages[] = [
                'image_uri' => $image->getImageUri(),
                'sort_order' => $image->getSortOrder()
            ];
        }


        return $this->render('animalline/breeder/member/pets/edit.twig', [
            'breeder_pet' => $breederPet,
            'pet_mages' => $petImages,
            'form' => $form->createView()
        ]);
    }

    /**
     * Copy image and retrieve new url of the copy
     *
     * @param string $imageUrl
     * @param int $petId
     * @return string
     */
    private function setImageSrc($imageUrl, $petId)
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
}
