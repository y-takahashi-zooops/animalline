<?php

namespace Customize\Controller\Breeder;

use Carbon\Carbon;
use Customize\Config\AnilineConf;
use Customize\Entity\BreederContacts;
use Customize\Entity\BreederHouse;
use Customize\Entity\BreederPetImage;
use Customize\Entity\BreederPets;
use Customize\Form\Type\Breeder\BreederHouseType;
use Customize\Form\Type\BreederPetsType;
use Customize\Form\Type\BreedersType;
use Customize\Repository\BreederContactsRepository;
use Customize\Repository\BreederHouseRepository;
use Customize\Repository\BreederPetsRepository;
use Customize\Repository\BreederPetImageRepository;
use Customize\Repository\BreedersRepository;
use Customize\Repository\BreedsRepository;
use Customize\Repository\CoatColorsRepository;
use Eccube\Controller\AbstractController;
use Eccube\Event\EccubeEvents;
use Eccube\Repository\Master\PrefRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception as HttpException;
use Symfony\Component\Routing\Annotation\Route;
use Eccube\Event\EventArgs;
use DateTime;

class BreederConfigrationController extends AbstractController
{
    /**
     * @var BreederContactsRepository
     */
    protected $breederContactsRepository;

    /**
     * @var BreederPetsRepository
     */
    protected $breederPetsRepository;

    /**
     * @var BreederPetImageRepository
     */
    protected $breederPetImageRepository;

    /**
     * @var BreederHouseRepository
     */
    protected $breederHouseRepository;

    /**
     * BreederConfigrationController constructor.
     * @param BreederContactsRepository $breederContactsRepository
     * @param BreederPetsRepository $breederPetsRepository
     * @param BreederPetImageRepository $breederPetImageRepository
     * @param BreederHouseRepository $breederHouseRepository
     */
    public function __construct(
        BreederContactsRepository $breederContactsRepository,
        BreederPetsRepository     $breederPetsRepository,
        BreederPetImageRepository $breederPetImageRepository,
        BreederHouseRepository    $breederHouseRepository
    )
    {
        $this->breederContactsRepository = $breederContactsRepository;
        $this->breederPetsRepository = $breederPetsRepository;
        $this->breederPetImageRepository = $breederPetImageRepository;
        $this->breederHouseRepository = $breederHouseRepository;
    }

    /**
     * @Route("/breeder/configration/all_message", name="get_message_breeder_configration")
     * @Template("animalline/breeder/configration/get_message.twig")
     */
    public function get_message_breeder_configration(Request $request)
    {
        $rootMessages = $this->breederContactsRepository->findBy(
            [
                'parent_message_id' => AnilineConf::ROOT_MESSAGE_ID,
                'Breeder' => $this->getUser()
            ],
            ['is_response' => 'ASC', 'send_date' => 'DESC']
        );

        $lastReplies = [];
        foreach ($rootMessages as $message) {
            $lastReply = $this->breederContactsRepository->findOneBy(
                ['parent_message_id' => $message->getId()],
                ['send_date' => 'DESC']
            );
            $lastReplies[$message->getId()] = $lastReply ? $lastReply->getSendDate() : null;
        }

        $pets = $this->breederPetsRepository->findBy(['Breeder' => $this->getUser()], ['update_date' => 'DESC']);

        return $this->render(
            'animalline/breeder/configration/get_message.twig',
            [
                'rootMessages' => $rootMessages,
                'lastReplies' => $lastReplies,
                'breeder' => $this->getUser(),
                'pets' => $pets
            ]
        );
    }

    /**
     * 保護団体管理ページTOP
     *
     * @Route("/breeder/configration/", name="breeder_configration")
     * @Template("animalline/breeder/configration/index.twig")
     */
    public function breeder_configration(Request $request)
    {
        $rootMessages = $this->breederContactsRepository->findBy(
            [
                'parent_message_id' => AnilineConf::ROOT_MESSAGE_ID,
                'Breeder' => $this->getUser(),
                'contract_status' => AnilineConf::CONTRACT_STATUS_UNDER_NEGOTIATION
            ],
            ['is_response' => 'ASC', 'send_date' => 'DESC']
        );

        $lastReplies = [];
        foreach ($rootMessages as $message) {
            $lastReply = $this->breederContactsRepository->findOneBy(
                ['parent_message_id' => $message->getId()],
                ['send_date' => 'DESC']
            );
            $lastReplies[$message->getId()] = $lastReply ? $lastReply->getSendDate() : null;
        }

        $pets = $this->breederPetsRepository->findBy(['Breeder' => $this->getUser()], ['update_date' => 'DESC']);

        return $this->render(
            'animalline/breeder/configration/index.twig',
            [
                'rootMessages' => $rootMessages,
                'lastReplies' => $lastReplies,
                'breeder' => $this->getUser(),
                'pets' => $pets
            ]
        );
    }

    /**
     * @Route("/breeder/configration/message/{contact_id}", name="breeder_configration_messages", requirements={"contact_id" = "\d+"})
     * @Template("animalline/breeder/configration/message.twig")
     */
    public function breeder_configration_message(Request $request, $contact_id)
    {
        $rootMessage = $this->breederContactsRepository->find($contact_id);
        if (!$rootMessage) {
            throw new HttpException\NotFoundHttpException();
        }

        $description = $request->get('contact_description');

        $breederContact = new BreederContacts();
        $form = $this->createFormBuilder($breederContact)->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $breederContact->setCustomer($rootMessage->getCustomer())
                ->setBreeder($this->getUser())
                ->setMessageFrom(AnilineConf::MESSAGE_FROM_CONFIGURATION)
                ->setPet($rootMessage->getPet())
                ->setContactType(AnilineConf::CONTACT_TYPE_REPLY)
                ->setContactDescription($description)
                ->setParentMessageId($contact_id)
                ->setSendDate(new DateTime())
                ->setIsResponse(AnilineConf::RESPONSE_REPLIED)
                ->setContractStatus(AnilineConf::CONTRACT_STATUS_UNDER_NEGOTIATION);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($breederContact);
            $entityManager->flush();
        }
        $messages = $this->breederContactsRepository->findBy(
            ['parent_message_id' => $contact_id],
            ['send_date' => 'ASC']
        );
        return $this->render('animalline/breeder/configration/message.twig', [
            'rootMessage' => $rootMessage,
            'messages' => $messages,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/breeder/configration/pets/new/{breeder_id}", name="breeder_configuration_pets_new", methods={"GET","POST"})
     */
    public function breeder_configuration_pets_new(Request $request, BreedersRepository $breedersRepository): Response
    {
        $breederPet = new BreederPets();
        $form = $this->createForm(BreederPetsType::class, $breederPet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $breeder = $breedersRepository->find($request->get('breeder_id'));
            $breederPet->setBreeder($breeder);
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

            $entityManager->persist($petImage0);
            $entityManager->persist($petImage1);
            $entityManager->persist($petImage2);
            $entityManager->persist($petImage3);
            $entityManager->persist($petImage4);
            $entityManager->persist($breederPet);
            $entityManager->flush();

            return $this->redirectToRoute('breeder_configration');
        }

        return $this->render('animalline/breeder/configration/pets/new.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/breeder/configration/pets/edit/{id}", name="breeder_configuration_pets_edit", methods={"GET","POST"})
     */
    public function breeder_configuration_pets_edit(Request $request, BreederPets $breederPet): Response
    {
        $form = $this->createForm(BreederPetsType::class, $breederPet);
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

            return $this->redirectToRoute('breeder_configration');
        }

        $petImages = [];
        foreach ($breederPetImages as $image) {
            $petImages[] = [
                'image_uri' => $image->getImageUri(),
                'sort_order' => $image->getSortOrder()
            ];
        }

        return $this->render('animalline/breeder/configration/pets/edit.twig', [
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

    /**
     * @Route("/breeder_pet_data_by_pet_kind", name="breeder_pet_data_by_pet_kind", methods={"GET"})
     */
    public function breederPetDataByPetKind(Request $request, BreedsRepository $breedsRepository, CoatColorsRepository $coatColorsRepository)
    {
        $petKind = $request->get('pet_kind');
        $breeds = $breedsRepository->findBy(['pet_kind' => $petKind]);
        $colors = $coatColorsRepository->findBy(['pet_kind' => $petKind]);
        $formattedBreeds = [];
        foreach ($breeds as $breed) {
            $formattedBreeds[] = [
                'id' => $breed->getId(),
                'name' => $breed->getBreedsName()
            ];
        }
        $formattedColors = [];
        foreach ($colors as $color) {
            $formattedColors[] = [
                'id' => $color->getId(),
                'name' => $color->getCoatColorName()
            ];
        }
        $data = [
            'breeds' => $formattedBreeds,
            'colors' => $formattedColors
        ];

        return new JsonResponse($data);
    }

    /**
     * @Route("/breeder/configration/pets/upload", name="breeder_pets_upload_crop_image", methods={"POST"}, options={"expose"=true})
     * @param Request $request
     * @return JsonResponse
     */
    public function upload(Request $request)
    {
        if (!file_exists(AnilineConf::ANILINE_IMAGE_URL_BASE . '/tmp/')) {
            mkdir(AnilineConf::ANILINE_IMAGE_URL_BASE . '/tmp/', 0777, 'R');
        }
        $folderPath = AnilineConf::ANILINE_IMAGE_URL_BASE . '/tmp/';
        $image_parts = explode(";base64,", $_POST['image']);
        $image_type_aux = explode("image/", $image_parts[0]);
        $image_type = $image_type_aux[1];
        $image_base64 = base64_decode($image_parts[1]);
        $file = $folderPath . uniqid() . '.' . $image_type;
        file_put_contents($file, $image_base64);
        return new JsonResponse($file);
    }

    /**
     * @Route("/breeder/configration/baseinfo", name="breeder_baseinfo")
     * @Template("/animalline/breeder/configration/baseinfo.twig")
     */
    public function baseinfo(Request $request)
    {
        return [];
    }

    /**
     * @Route("/breeder/configration/houseinfo/{pet_type}", name="breeder_houseinfo")
     * @Template("/animalline/breeder/configration/houseinfo.twig")
     */
    public function houseinfo(Request $request)
    {
        $petType = $request->get('pet_type');
        $breederHousePet = $this->breederHouseRepository->findOneBy(['pet_type' => $petType, 'Breeder' => $this->getUser()]);
        $breederHouse = new BreederHouse();
        $builder = $this->formFactory->createBuilder(BreederHouseType::class, $breederHousePet??$breederHouse);

        $form = $builder->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
                    if (!$breederHousePet) {
                        $housePref = $breederHouse->getBreederHousePrefId();
                        $breederHouse->setBreeder($this->getUser())
                            ->setPetType($petType)
                            ->setBreederHousePref($housePref['name']);
                        $entityManager = $this->getDoctrine()->getManager();
                        $entityManager->persist($breederHouse);
                    }else{
                        $housePref = $breederHousePet->getBreederHousePrefId();
                        $breederHousePet->setBreederHousePref($housePref['name']);
                        $entityManager = $this->getDoctrine()->getManager();
                        $entityManager->persist($breederHousePet);
                    }

                    $entityManager->flush();

                    return $this->redirectToRoute('breeder_house_complete', ['pet_type' => $petType]);
        }
        return [
            'form' => $form->createView(),
            'petType' => $petType,
        ];
    }

    /**
     * @Route("/breeder/configration/houseinfo/{pet_type}/complete", name="breeder_house_complete", requirements={"pet_id" = "\d+"})
     * @Template("/animalline/breeder/configration/houseinfo_complete.twig")
     */
    public function complete(Request $request)
    {
        return $this->render('/animalline/breeder/configration/houseinfo_complete.twig', [
            'petType' => $request->get('pet_type')
        ]);
    }

    /**
     * @Route("/breeder/configration/examinationinfo/{pet_type}", name="breeder_examinationinfo")
     * @Template("/animalline/breeder/configration/examinationinfo.twig")
     */
    public function examinationinfo(Request $request)
    {
        return [];
    }
}
