<?php

namespace Customize\Controller\Adoption;

use Customize\Config\AnilineConf;
use Customize\Entity\ConservationPetImage;
use Customize\Entity\ConservationPets;
use Customize\Repository\DnaCheckStatusHeaderRepository;
use Customize\Repository\DnaCheckStatusRepository;
use Customize\Service\AdoptionQueryService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Customize\Form\Type\Adoption\ConservationPetsType;
use Customize\Repository\ConservationPetsRepository;
use Customize\Repository\PetsFavoriteRepository;
use Eccube\Repository\Master\PrefRepository;
use Customize\Repository\ConservationContactHeaderRepository;
use Customize\Repository\ConservationContactsRepository;
use Customize\Repository\SendoffReasonRepository;
use Customize\Repository\ConservationsRepository;
use Customize\Repository\ConservationsHousesRepository;
use Customize\Repository\ConservationPetImageRepository;

use Customize\Service\DnaQueryService;
use Eccube\Repository\CustomerRepository;
use Eccube\Controller\AbstractController;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception as HttpException;

class ConservationPetController extends AbstractController
{
    /**
     * @var ConservationPetsRepository
     */
    protected $conservationPetsRepository;

    /**
     * @var DnaCheckStatusRepository
     */
    protected $dnaCheckStatusRepository;

    /**
     * @var PetsFavoriteRepository
     */
    protected $petsFavoriteRepository;

    /**
     * @var ConservationsRepository
     */
    protected $conservationsRepository;

    /**
     * @var ConservationPetImageRepository
     */
    protected $conservationPetImageRepository;

    /**
     * @var DnaCheckStatusHeaderRepository
     */
    protected $dnaCheckStatusHeaderRepository;

    /**
     * ConservationController constructor.
     *
     * @param ConservationPetsRepository $conservationPetsRepository
     * @param DnaCheckStatusRepository $dnaCheckStatusRepository
     * @param ConservationsRepository $conservationsRepository
     * @param ConservationPetImageRepository $conservationPetImageRepository
     * @param PetsFavoriteRepository $petsFavoriteRepository
     * @param DnaCheckStatusHeaderRepository $dnaCheckStatusHeaderRepository
     */
    public function __construct(
        ConservationPetsRepository     $conservationPetsRepository,
        DnaCheckStatusRepository       $dnaCheckStatusRepository,
        ConservationsRepository        $conservationsRepository,
        ConservationPetImageRepository $conservationPetImageRepository,
        PetsFavoriteRepository         $petsFavoriteRepository,
        DnaCheckStatusHeaderRepository $dnaCheckStatusHeaderRepository
    )
    {
        $this->conservationPetsRepository = $conservationPetsRepository;
        $this->dnaCheckStatusRepository = $dnaCheckStatusRepository;
        $this->conservationsRepository = $conservationsRepository;
        $this->conservationPetImageRepository = $conservationPetImageRepository;
        $this->petsFavoriteRepository = $petsFavoriteRepository;
        $this->dnaCheckStatusHeaderRepository = $dnaCheckStatusHeaderRepository;
    }

    /**
     * 新規ペット追加
     *
     * @Route("/adoption/member/pets/new/{bar_code}", name="adoption_pets_new", methods={"GET","POST"}, requirements={"bar_code" = "^\d{6}$"})
     * @param Request $request
     * @return Response
     */
    public function adoption_pets_new(Request $request): Response
    {
        $barCode = substr($request->get('bar_code'), 1);
        $DnaId = (int)$barCode;
        $Dna = $this->dnaCheckStatusRepository->find($DnaId);
        if (!$Dna) {
            throw new HttpException\NotFoundHttpException();
        }
        $user = $this->getUser();
        $is_conservation = $user->getIsConservation();
        $DnaHeader = $Dna->getDnaHeader();
        $conservation = $this->conservationsRepository->find($DnaHeader->getRegisterId());
        if ($is_conservation == 0) {
            return $this->render('animalline/adopution/member/examination_guidance.twig', [
                'conservation' => $conservation
            ]);
        }

        $conservationPet = new ConservationPets();
        $form = $this->createForm(ConservationPetsType::class, $conservationPet, [
            'customer' => $this->getUser(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $conservationPet->setConservation($conservation);
            $conservationPet->setDnaCheckResult(0);
            $conservationPet->setReleaseStatus(0);
            $conservationPet->setPrice(0);
            $entityManager->persist($conservationPet);
            $entityManager->flush();
            $petId = $conservationPet->getId();
            $Dna->setPetId($petId)
                ->setCheckStatus(AnilineConf::ANILINE_DNA_CHECK_STATUS_PET_REGISTERED)
                ->setKitPetRegisterDate(new \DateTime);
            $entityManager->persist($Dna);
            $entityManager->flush();
            $img0 = $this->setImageSrc($request->get('img0'), $petId);
            $img1 = $this->setImageSrc($request->get('img1'), $petId);
            $img2 = $this->setImageSrc($request->get('img2'), $petId);
            $img3 = $this->setImageSrc($request->get('img3'), $petId);
            $img4 = $this->setImageSrc($request->get('img4'), $petId);

            $petImage0 = (new ConservationPetImage())
                ->setImageType(AnilineConf::PET_PHOTO_TYPE_IMAGE)->setImageUri($img0)->setSortOrder(1)
                ->setConservationPet($conservationPet);
            $petImage1 = (new ConservationPetImage())
                ->setImageType(AnilineConf::PET_PHOTO_TYPE_IMAGE)->setImageUri($img1)->setSortOrder(2)
                ->setConservationPet($conservationPet);
            $petImage2 = (new ConservationPetImage())
                ->setImageType(AnilineConf::PET_PHOTO_TYPE_IMAGE)->setImageUri($img2)->setSortOrder(3)
                ->setConservationPet($conservationPet);
            $petImage3 = (new ConservationPetImage())
                ->setImageType(AnilineConf::PET_PHOTO_TYPE_IMAGE)->setImageUri($img3)->setSortOrder(4)
                ->setConservationPet($conservationPet);
            $petImage4 = (new ConservationPetImage())
                ->setImageType(AnilineConf::PET_PHOTO_TYPE_IMAGE)->setImageUri($img4)->setSortOrder(5)
                ->setConservationPet($conservationPet);
            $conservationPet->addConservationPetImage($petImage0);
            $conservationPet->addConservationPetImage($petImage1);
            $conservationPet->addConservationPetImage($petImage2);
            $conservationPet->addConservationPetImage($petImage3);
            $conservationPet->addConservationPetImage($petImage4);
            $conservationPet->setThumbnailPath($img0);

            // $dnaCheckStatus = (new DnaCheckStatus)
            //     ->setRegisterId($conservation->getId())
            //     ->setPetId($conservationPet->getId())
            //     ->setSiteType(AnilineConf::ANILINE_SITE_TYPE_ADOPTION);

            $entityManager->persist($petImage0);
            $entityManager->persist($petImage1);
            $entityManager->persist($petImage2);
            $entityManager->persist($petImage3);
            $entityManager->persist($petImage4);
            $entityManager->persist($conservationPet);
            // $entityManager->persist($dnaCheckStatus);
            $entityManager->flush();

            return $this->redirectToRoute('adoption_newpet_complete');
        }

        return $this->render('animalline/adoption/member/pets/new.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     *
     * 新規ペット追加完了メッセージ
     *
     * @Route("/breeder/adoption/pets/new_complete", name="adoption_newpet_complete", methods={"GET","POST"})
     * @Template("animalline/adoption/member/pets/notification.twig")
     */
    public function adoption_pets_new_complete()
    {
        return [];
    }

    /**
     * ペット情報編集
     *
     * @Route("/adoption/member/pets/edit/{id}", name="adoption_pets_edit", methods={"GET","POST"})
     * @param Request $request
     * @param ConservationPets $conservationPet
     * @return Response
     */
    public function adoption_pets_edit(Request $request, ConservationPets $conservationPet): Response
    {
        $form = $this->createForm(ConservationPetsType::class, $conservationPet, [
            'customer' => $this->getUser(),
        ]);
        $conservationPetImages = $this->conservationPetImageRepository->findBy(
            ['ConservationPet' => $conservationPet, 'image_type' => AnilineConf::PET_PHOTO_TYPE_IMAGE],
            ['sort_order' => 'ASC']
        );
        $request->request->set('thumbnail_path', $conservationPet->getThumbnailPath());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $petId = $conservationPet->getId();
            $img0 = $this->setImageSrc($request->get('img0'), $petId);
            $img1 = $this->setImageSrc($request->get('img1'), $petId);
            $img2 = $this->setImageSrc($request->get('img2'), $petId);
            $img3 = $this->setImageSrc($request->get('img3'), $petId);
            $img4 = $this->setImageSrc($request->get('img4'), $petId);
            $entityManager = $this->getDoctrine()->getManager();
            $conservationPet->setThumbnailPath($img0);
            $entityManager->persist($conservationPet);
            foreach ($conservationPetImages as $key => $image) {
                $image->setImageUri(${'img' . $key});
                $entityManager->persist($image);
            }
            $entityManager->flush();

            return $this->redirectToRoute('adoption_pet_list');
        }

        $petImages = [];
        foreach ($conservationPetImages as $image) {
            $petImages[] = [
                'image_uri' => $image->getImageUri(),
                'sort_order' => $image->getSortOrder()
            ];
        }

        return $this->render('animalline/adoption/member/pets/edit.twig', [
            'adoption_pet' => $conservationPet,
            'pet_mages' => $petImages,
            'form' => $form->createView()
        ]);
    }

    /**
     * 取扱ペット一覧TOP
     *
     * @Route("/adoption/member/pet_list", name="adoption_pet_list")
     * @Template("animalline/adoption/member/pet_list.twig")
     */
    public function adoption_pet_list(Request $request)
    {
        $pets = $this->conservationPetsRepository->findBy(['Conservation' => $this->getUser()], ['update_date' => 'DESC']);

        return $this->render(
            'animalline/adoption/member/pet_list.twig',
            [
                'conservation' => $this->getUser(),
                'pets' => $pets,
            ]
        );
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
        if ($arr[0] === 'adoption') {
            return $resource;
        }

        $imageName = str_replace(
            AnilineConf::ANILINE_IMAGE_URL_BASE . '/tmp/',
            '',
            $imageUrl
        );
        $subUrl = AnilineConf::ANILINE_IMAGE_URL_BASE . '/adoption/' . $petId . '/';
        if (!file_exists($subUrl)) {
            mkdir($subUrl, 0777, 'R');
        }

        copy($imageUrl, $subUrl . $imageName);
        return '/adoption/' . $petId . '/' . $imageName;
    }

    /**
     * ペット詳細
     *
     * @Route("/adoption/pet/detail/{id}", name="adoption_pet_detail", requirements={"id" = "\d+"})
     * @Template("animalline/adoption/pet/detail.twig")
     */
    public function petDetail(Request $request)
    {
        $isLoggedIn = (bool)$this->getUser();
        $id = $request->get('id');
        $isFavorite = false;
        $conservationPet = $this->conservationPetsRepository->find($id);
        $favorite = $this->petsFavoriteRepository->findOneBy(['Customer' => $this->getUser(), 'pet_id' => $id]);
        if ($favorite) {
            $isFavorite = true;
        }
        if (!$conservationPet) {
            throw new HttpException\NotFoundHttpException();
        }

        $images = $this->conservationPetImageRepository->findBy(
            [
                'ConservationPet' => $conservationPet,
                'image_type' => AnilineConf::PET_PHOTO_TYPE_IMAGE
            ]
        );
        $video = $this->conservationPetImageRepository->findOneBy(
            [
                'ConservationPet' => $conservationPet,
                'image_type' => AnilineConf::PET_PHOTO_TYPE_VIDEO
            ]
        );

        return $this->render(
            'animalline/adoption/pet/detail.twig',
            [
                'conservationPet' => $conservationPet,
                'images' => $images,
                'video' => $video,
                'isFavorite' => $isFavorite,
                'isLoggedIn' => $isLoggedIn
            ]
        );
    }

    /**
     *
     * ペット登録一覧
     *
     * @Route("/adoption/member/pet_regist_list", name="adoption_pet_regist_list")
     * @Template("animalline/adoption/member/pets/regist_list.twig")
     */
    public function pet_regist_list(Request $request, PaginatorInterface $paginator)
    {
        $codes = [];
        $dnaCheckStatusHeaders = $this->dnaCheckStatusHeaderRepository->findBy(['register_id'=>$this->getUser()->getId()]);
        $DnaCheckStatus = $this->dnaCheckStatusRepository->createQueryBuilder('dcs')
            ->where('dcs.DnaHeader IN(:arr)')
            ->andWhere('dcs.site_type = :siteType')
            ->andWhere('dcs.check_status = :checkStatus')
            ->setParameter('siteType', AnilineConf::ANILINE_SITE_TYPE_ADOPTION)
            ->setParameter('checkStatus', AnilineConf::ANILINE_DNA_CHECK_STATUS_SHIPPING)
            ->setParameter('arr', $dnaCheckStatusHeaders)
            ->addOrderBy('dcs.update_date', 'DESC')
            ->getQuery()->getResult();
        foreach ($DnaCheckStatus as $dnaCheckStatus)
            $codes[] = '2' . str_pad($dnaCheckStatus->getId(), 5, '0', STR_PAD_LEFT);
        $barCodes = $paginator->paginate(
            $codes,
            $request->query->getInt('page', 1),
            AnilineConf::ANILINE_NUMBER_ITEM_PER_PAGE
        );
        return compact('barCodes');
    }
}
