<?php

namespace Customize\Controller\Breeder;

use Customize\Repository\BreederContactsRepository;
use Customize\Repository\BreederExaminationInfoRepository;
use Eccube\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception as HttpException;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

class BreederConfigrationController extends AbstractController
{
    /**
     * @var BreederContactsRepository
     */
    protected $breederContactsRepository;

    /**
     * @var BreederExaminationInfoRepository
     */
    protected $breederExaminationInfoRepository;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * BreederConfigrationController constructor.
     * @param BreederContactsRepository $breederContactsRepository
     * @param BreederExaminationInfoRepository $breederExaminationInfoRepository
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        BreederContactsRepository        $breederContactsRepository,
        BreederExaminationInfoRepository $breederExaminationInfoRepository
    ) {
        $this->breederContactsRepository = $breederContactsRepository;
        $this->breederExaminationInfoRepository = $breederExaminationInfoRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * 保護団体管理ページTOP
     *
     * @Route("/breeder/configration/", name="breeder_configration")
     * @Template("animalline/breeder/configration/index.twig")
     */
    public function breeder_configration(Request $request)
    {
        // $rootMessages = $this->breederContactsRepository->findBy(
        //     [
        //         'parent_message_id' => AnilineConf::ROOT_MESSAGE_ID,
        //         'Breeder' => $this->getUser(),
        //         'contract_status' => AnilineConf::CONTRACT_STATUS_UNDER_NEGOTIATION
        //     ],
        //     ['is_response' => 'ASC', 'send_date' => 'DESC']
        // );

        // $lastReplies = [];
        // foreach ($rootMessages as $message) {
        //     $lastReply = $this->breederContactsRepository->findOneBy(
        //         ['parent_message_id' => $message->getId()],
        //         ['send_date' => 'DESC']
        //     );
        //     $lastReplies[$message->getId()] = $lastReply ? $lastReply->getSendDate() : null;
        // }

        $pets = $this->breederPetsRepository->findBy(['Breeder' => $this->getUser()], ['update_date' => 'DESC']);

        return $this->render(
            'animalline/breeder/configration/index.twig',
            [
                // 'rootMessages' => $rootMessages,
                // 'lastReplies' => $lastReplies,
                'breeder' => $this->getUser(),
                'pets' => $pets,
            ]
        );
    }

    /**
     * Page pet new's Breeder
     * 
     * @Route("/breeder/configration/pets/new/{breeder_id}", name="breeder_configuration_pets_new", methods={"GET","POST"})
     */
    // public function breeder_configuration_pets_new(Request $request, BreedersRepository $breedersRepository): Response
    // {
    //     $breederPet = new BreederPets();
    //     $form = $this->createForm(BreederPetsType::class, $breederPet);
    //     $form->handleRequest($request);

    //     if ($form->isSubmitted() && $form->isValid()) {
    //         $entityManager = $this->getDoctrine()->getManager();
    //         $breeder = $breedersRepository->find($request->get('breeder_id'));
    //         $breederPet->setBreeder($breeder);
    //         $breederPet->setDnaCheckResult(0);
    //         $breederPet->setIsActive(1);
    //         $entityManager->persist($breederPet);
    //         $entityManager->flush();
    //         $petId = $breederPet->getId();
    //         $img0 = $this->setImageSrc($request->get('img0'), $petId);
    //         $img1 = $this->setImageSrc($request->get('img1'), $petId);
    //         $img2 = $this->setImageSrc($request->get('img2'), $petId);
    //         $img3 = $this->setImageSrc($request->get('img3'), $petId);
    //         $img4 = $this->setImageSrc($request->get('img4'), $petId);

    //         $petImage0 = (new BreederPetImage())
    //             ->setImageType(AnilineConf::PET_PHOTO_TYPE_IMAGE)->setImageUri($img0)->setSortOrder(1)
    //             ->setBreederPet($breederPet);
    //         $petImage1 = (new BreederPetImage())
    //             ->setImageType(AnilineConf::PET_PHOTO_TYPE_IMAGE)->setImageUri($img1)->setSortOrder(2)
    //             ->setBreederPet($breederPet);
    //         $petImage2 = (new BreederPetImage())
    //             ->setImageType(AnilineConf::PET_PHOTO_TYPE_IMAGE)->setImageUri($img2)->setSortOrder(3)
    //             ->setBreederPet($breederPet);
    //         $petImage3 = (new BreederPetImage())
    //             ->setImageType(AnilineConf::PET_PHOTO_TYPE_IMAGE)->setImageUri($img3)->setSortOrder(4)
    //             ->setBreederPet($breederPet);
    //         $petImage4 = (new BreederPetImage())
    //             ->setImageType(AnilineConf::PET_PHOTO_TYPE_IMAGE)->setImageUri($img4)->setSortOrder(5)
    //             ->setBreederPet($breederPet);
    //         $breederPet
    //             ->addBreederPetImage($petImage0)
    //             ->addBreederPetImage($petImage1)
    //             ->addBreederPetImage($petImage2)
    //             ->addBreederPetImage($petImage3)
    //             ->addBreederPetImage($petImage4)
    //             ->setThumbnailPath($img0);

    //         $entityManager->persist($petImage0);
    //         $entityManager->persist($petImage1);
    //         $entityManager->persist($petImage2);
    //         $entityManager->persist($petImage3);
    //         $entityManager->persist($petImage4);
    //         $entityManager->persist($breederPet);
    //         $entityManager->flush();

    //         return $this->redirectToRoute('breeder_pet_list');
    //     }

    //     return $this->render('animalline/breeder/configration/pets/new.twig', [
    //         'form' => $form->createView()
    //     ]);
    // }

    /**
     * Breeder edit pets
     * 
     * @Route("/breeder/configration/pets/edit/{id}", name="breeder_configuration_pets_edit", methods={"GET","POST"})
     */
    // public function breeder_configuration_pets_edit(Request $request, BreederPets $breederPet): Response
    // {
    //     $form = $this->createForm(BreederPetsType::class, $breederPet);
    //     $breederPetImages = $this->breederPetImageRepository->findBy(
    //         ['BreederPets' => $breederPet, 'image_type' => AnilineConf::PET_PHOTO_TYPE_IMAGE],
    //         ['sort_order' => 'ASC']
    //     );
    //     $request->request->set('thumbnail_path', $breederPet->getThumbnailPath());
    //     $form->handleRequest($request);

    //     if ($form->isSubmitted() && $form->isValid()) {
    //         $petId = $breederPet->getId();
    //         $img0 = $this->setImageSrc($request->get('img0'), $petId);
    //         $img1 = $this->setImageSrc($request->get('img1'), $petId);
    //         $img2 = $this->setImageSrc($request->get('img2'), $petId);
    //         $img3 = $this->setImageSrc($request->get('img3'), $petId);
    //         $img4 = $this->setImageSrc($request->get('img4'), $petId);
    //         $entityManager = $this->getDoctrine()->getManager();
    //         $breederPet->setThumbnailPath($img0);
    //         $entityManager->persist($breederPet);
    //         foreach ($breederPetImages as $key => $image) {
    //             $image->setImageUri(${'img' . $key});
    //             $entityManager->persist($image);
    //         }
    //         $entityManager->flush();

    //         return $this->redirectToRoute('breeder_pet_list');
    //     }

    //     $petImages = [];
    //     foreach ($breederPetImages as $image) {
    //         $petImages[] = [
    //             'image_uri' => $image->getImageUri(),
    //             'sort_order' => $image->getSortOrder()
    //         ];
    //     }

    //     return $this->render('animalline/breeder/configration/pets/edit.twig', [
    //         'breeder_pet' => $breederPet,
    //         'pet_mages' => $petImages,
    //         'form' => $form->createView()
    //     ]);
    // }

    /**
     * Copy image and retrieve new url of the copy
     *
     * @param string $imageUrl
     * @param int $petId
     * @return string
     */
    private
    function setImageSrc($imageUrl, $petId)
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
     * Get pet data by pet kind
     * 
     * @Route("/breeder_pet_data_by_pet_kind", name="breeder_pet_data_by_pet_kind", methods={"GET"})
     */
    // public function breederPetDataByPetKind(Request $request, BreedsRepository $breedsRepository, CoatColorsRepository $coatColorsRepository)
    // {
    //     $petKind = $request->get('pet_kind');
    //     $breeds = $breedsRepository->findBy(['pet_kind' => $petKind]);
    //     $colors = $coatColorsRepository->findBy(['pet_kind' => $petKind]);
    //     $formattedBreeds = [];
    //     foreach ($breeds as $breed) {
    //         $formattedBreeds[] = [
    //             'id' => $breed->getId(),
    //             'name' => $breed->getBreedsName()
    //         ];
    //     }
    //     $formattedColors = [];
    //     foreach ($colors as $color) {
    //         $formattedColors[] = [
    //             'id' => $color->getId(),
    //             'name' => $color->getCoatColorName()
    //         ];
    //     }
    //     $data = [
    //         'breeds' => $formattedBreeds,
    //         'colors' => $formattedColors
    //     ];

    //     return new JsonResponse($data);
    // }

    /**
     * Upload image
     * 
     * @Route("/breeder/configration/pets/upload", name="breeder_pets_upload_crop_image", methods={"POST"}, options={"expose"=true})
     */
    // public function upload(Request $request)
    // {
    //     if (!file_exists(AnilineConf::ANILINE_IMAGE_URL_BASE . '/tmp/')) {
    //         mkdir(AnilineConf::ANILINE_IMAGE_URL_BASE . '/tmp/', 0777, 'R');
    //     }
    //     $folderPath = AnilineConf::ANILINE_IMAGE_URL_BASE . '/tmp/';
    //     $image_parts = explode(";base64,", $_POST['image']);
    //     $image_type_aux = explode("image/", $image_parts[0]);
    //     $image_type = $image_type_aux[1];
    //     $image_base64 = base64_decode($image_parts[1]);
    //     $file = $folderPath . uniqid() . '.' . $image_type;
    //     file_put_contents($file, $image_base64);
    //     return new JsonResponse($file);
    // }

    /**
     * Breeder base information
     * 
     * @Route("/breeder/configration/baseinfo", name="breeder_baseinfo")
     * @Template("/animalline/breeder/configration/baseinfo.twig")
     */
    // public function baseinfo(Request $request, BreedersRepository $breedersRepository)
    // {
    //     $user = $this->getUser();

    //     $breederData = $breedersRepository->find($user);
    //     if (!$breederData) {
    //         $breederData = new Breeders;
    //         $breederData->setId($user->getId());
    //     }
    //     $builder = $this->formFactory->createBuilder(BreedersType::class, $breederData);

    //     $form = $builder->getForm();
    //     $form->handleRequest($request);

    //     if ($form->isSubmitted() && $form->isValid()) {
    //         $thumbnail_path = $request->get('thumbnail_path') ? $request->get('thumbnail_path') : $breederData->getThumbnailPath();

    //         $breederData->setBreederPref($breederData->getPrefBreeder())
    //             ->setLicensePref($breederData->getPrefLicense())
    //             ->setThumbnailPath($thumbnail_path);
    //         $entityManager = $this->getDoctrine()->getManager();
    //         $entityManager->persist($breederData);
    //         $entityManager->flush();
    //         return $this->redirectToRoute('breeder_member');
    //     }

    //     return [
    //         'breederData' => $breederData,
    //         'form' => $form->createView()
    //     ];
    // }

    /**
     * Page examination infomation's breeder
     * 
     * @Route("/breeder/configration/examinationinfo/{pet_type}", name="breeder_examinationinfo", methods={"GET","POST"})
     * @Template("/animalline/breeder/configration/examinationinfo.twig")
     */
    public function examinationinfo(Request $request)
    {
        $petType = $request->get('pet_type');
        $breeder = $this->getUser();
        $breederExaminationInfo = $this->breederExaminationInfoRepository->findOneBy([
            'Breeder' => $breeder,
            'pet_type' => $petType
        ]);
        $isEdit = false;
        if ($breederExaminationInfo) {
            $isEdit = true;
            if (in_array(
                $breederExaminationInfo->getPedigreeOrganization(),
                [AnilineConf::PEDIGREE_ORGANIZATION_JKC, AnilineConf::PEDIGREE_ORGANIZATION_KC]
            )) {
                $breederExaminationInfo->setGroupOrganization($breederExaminationInfo->getPedigreeOrganization());
                $breederExaminationInfo->setPedigreeOrganization(AnilineConf::PEDIGREE_ORGANIZATION_JKC);
            }
        } else {
            $breederExaminationInfo = new BreederExaminationInfo();
        }

        $form = $this->createForm(BreederExaminationInfoType::class, $breederExaminationInfo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $breederExaminationInfo->setPetType($petType)
                ->setBreeder($breeder);
            $formRequest = $request->request->get('breeder_examination_info');
            if ($formRequest['pedigree_organization'] == AnilineConf::PEDIGREE_ORGANIZATION_JKC) {
                $breederExaminationInfo->setPedigreeOrganization($formRequest['group_organization']);
            } else {
                $breederExaminationInfo->setPedigreeOrganization($formRequest['pedigree_organization']);
            }

            if ($formRequest['pedigree_organization'] != AnilineConf::PEDIGREE_ORGANIZATION_OTHER) {
                $breederExaminationInfo->setPedigreeOrganizationOther(null);
            }

            $entityManager = $this->entityManager;
            $entityManager->persist($breederExaminationInfo);
            $entityManager->flush();

            return $this->redirectToRoute('breeder_configration');
        }

        return $this->render('animalline/breeder/configration/examinationinfo.twig', [
            'form' => $form->createView(),
            'isEdit' => $isEdit,
            'petType' => $petType == AnilineConf::ANILINE_PET_KIND_DOG ? '犬' : '猫'
        ]);
    }
}
