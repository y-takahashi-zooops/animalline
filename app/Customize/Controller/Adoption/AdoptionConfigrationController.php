<?php

namespace Customize\Controller\Adoption;

use Carbon\Carbon;
use Customize\Config\AnilineConf;
use Customize\Entity\ConservationContactHeader;
use Customize\Entity\ConservationContacts;
use Customize\Entity\ConservationPets;
use Customize\Entity\ConservationPetImage;
use Customize\Entity\ConservationsHouse;
use Customize\Entity\DnaCheckStatus;
use Customize\Form\Type\ConservationHouseType;
use Customize\Form\Type\ConservationPetsType;
use Customize\Form\Type\ConservationsType;
use Customize\Repository\ConservationContactsRepository;
use Customize\Repository\ConservationContactHeaderRepository;
use Customize\Repository\ConservationsRepository;
use Customize\Repository\ConservationPetsRepository;
use Customize\Repository\BreedsRepository;
use Customize\Repository\CoatColorsRepository;
use Customize\Repository\ConservationPetImageRepository;
use Customize\Repository\ConservationsHousesRepository;
use Customize\Repository\SendoffReasonRepository;
use Eccube\Repository\Master\PrefRepository;
use Eccube\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception as HttpException;
use Symfony\Component\Routing\Annotation\Route;
use DateTime;

class AdoptionConfigrationController extends AbstractController
{
    /**
     * @var ConservationsRepository
     */
    protected $conservationsRepository;

    /**
     * @var ConservationPetsRepository
     */
    protected $conservationPetsRepository;

    /**
     * @var ConservationPetImageRepository
     */
    protected $conservationPetImageRepository;

    /**
     * @var ConservationContactsRepository
     */
    protected $conservationContactsRepository;

    /**
     * @var ConservationContactHeaderRepository
     */
    protected $conservationContactHeaderRepository;

    /**
     * @var ConservationsHousesRepository
     */
    protected $conservationsHousesRepository;

    /**
     * @var SendoffReasonRepository
     */
    protected $sendoffReasonRepository;


    /**
     * AdoptionConfigrationController constructor.
     */
    public function __construct(
        ConservationsRepository        $conservationsRepository,
        ConservationContactsRepository $conservationContactsRepository,
        ConservationContactHeaderRepository $conservationContactHeaderRepository,
        ConservationPetsRepository     $conservationPetsRepository,
        ConservationPetImageRepository $conservationPetImageRepository,
        ConservationsHousesRepository  $conservationsHousesRepository,
        SendoffReasonRepository        $sendoffReasonRepository
    ) {
        $this->conservationsRepository = $conservationsRepository;
        $this->conservationContactsRepository = $conservationContactsRepository;
        $this->conservationContactHeaderRepository = $conservationContactHeaderRepository;
        $this->conservationPetsRepository = $conservationPetsRepository;
        $this->conservationPetImageRepository = $conservationPetImageRepository;
        $this->conservationsHousesRepository = $conservationsHousesRepository;
        $this->sendoffReasonRepository = $sendoffReasonRepository;
    }

    /**
     * get message adoption configuration
     *
     * @Route("/adoption/configration/all_message", name="get_message_adoption_configration")
     * @Template("animalline/adoption/configration/get_message.twig")
     * @param Request $request
     * @return Response|null
     */
    public function get_message_adoption_configration(Request $request)
    {
        $rootMessages = $this->conservationContactHeaderRepository->findBy(
            [
                'Conservation' => $this->getUser()
            ],
            ['send_date' => 'DESC']
        );

        $name = [];
        foreach ($rootMessages as $message) {
            $name[$message->getId()] = "{$message->getCustomer()->getName01()} {$message->getCustomer()->getName02()}";
        }

        $pets = $this->conservationPetsRepository->findBy(['Conservation' => $this->getUser()], ['update_date' => 'DESC']);

        return $this->render(
            'animalline/adoption/configration/get_message.twig',
            [
                'rootMessages' => $rootMessages,
                'conservation' => $this->getUser(),
                'pets' => $pets,
                'name' => $name
            ]
        );
    }

    /**
     * 保護団体管理ページTOP
     *
     * @Route("/adoption/configration/", name="adoption_configration")
     * @Template("animalline/adoption/configration/index.twig")
     * @param Request $request
     * @return Response|null
     */
    public function adoption_configration(Request $request)
    {
        // $rootMessages = $this->conservationContactsRepository->findBy(
        //     [
        //         'parent_message_id' => AnilineConf::ROOT_MESSAGE_ID,
        //         'Conservation' => $this->getUser()
        //     ],
        //     ['is_response' => 'ASC', 'send_date' => 'DESC']
        // );

        $pets = $this->conservationPetsRepository->findBy(['Conservation' => $this->getUser()], ['update_date' => 'DESC']);

        return $this->render(
            'animalline/adoption/configration/index.twig',
            [
                // 'rootMessages' => $rootMessages,
                'conservation' => $this->getUser(),
                'pets' => $pets
            ]
        );
    }

    /**
     * 保護団体管理ページ - 取引メッセージ履歴
     *
     * @Route("/adoption/configration/message/{contact_id}", name="adoption_configration_messages", requirements={"contact_id" = "\d+"})
     * @Template("animalline/adoption/configration/message.twig")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response|null
     */
    public function adoption_configration_message(Request $request, $contact_id)
    {
        $isScroll = false;
        $isAcceptContract = $request->get('accept-contract');
        $reasonCancel = $request->get('reason');
        $replyMessage = $request->get('reply_message');
        $rootMessage = $this->conservationContactHeaderRepository->find($contact_id);
        if (!$rootMessage) {
            throw new HttpException\NotFoundHttpException();
        }
        $rootMessage->setConservationNewMsg(0);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($rootMessage);
        $entityManager->flush();
        $description = $request->get('reply_message');

        $conservationContact = new ConservationContacts();

        if ($replyMessage) {
            $conservationContact->setConservationHeader($rootMessage)
                ->setMessageFrom(AnilineConf::MESSAGE_FROM_CONFIGURATION)
                ->setContactDescription($description)
                ->setSendDate(new DateTime());
            $entityManager = $this->getDoctrine()->getManager();
            $rootMessage->setCustomerNewMsg(1)
                ->setLastMessageDate(Carbon::now());
            $entityManager->persist($conservationContact);
            $entityManager->persist($rootMessage);
            $entityManager->flush();
            $isScroll = true;
        }

        if ($isAcceptContract) {
            switch ($rootMessage->getContractStatus()) {
                case AnilineConf::CONTRACT_STATUS_UNDER_NEGOTIATION:
                    $rootMessage->setContractStatus(AnilineConf::CONTRACT_STATUS_WAITCONTRACT)
                        ->setConservationCheck(1);
                    break;
                case AnilineConf::CONTRACT_STATUS_WAITCONTRACT:
                    $rootMessage->setContractStatus(AnilineConf::CONTRACT_STATUS_WAITCONTRACT)
                        ->setConservationCheck(1);
                    break;
            }
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($rootMessage);
            $entityManager->flush();

            return $this->redirectToRoute('adoption_configration_messages', ['contact_id' => $rootMessage->getId()]);
        }

        if ($reasonCancel) {
            $rootMessage->setContractStatus(AnilineConf::CONTRACT_STATUS_NONCONTRACT)
                ->setCustomerNewMsg(1)
                ->setSendoffReason($reasonCancel);

            $conservationContact = (new ConservationContacts())
                ->setMessageFrom(AnilineConf::MESSAGE_FROM_USER)
                ->setContactDescription('今回の取引非成立となりました')
                ->setSendDate(Carbon::now())
                ->setConservationHeader($rootMessage);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($rootMessage);
            $entityManager->persist($conservationContact);
            $entityManager->flush();

            return $this->redirectToRoute('get_message_adoption_configration');
        }

        $messages = $this->conservationContactsRepository->findBy(
            ['ConservationHeader' => $contact_id],
            ['send_date' => 'ASC']
        );
        $reasons = $this->sendoffReasonRepository->findBy(['is_adoption_visible' => AnilineConf::ADOPTION_VISIBLE_SHOW]);

        return $this->render('animalline/adoption/configration/message.twig', [
            'rootMessage' => $rootMessage,
            'messages' => $messages,
            'reasons' => $reasons,
            'isScroll' => $isScroll
        ]);
    }

    /**
     * adoption configuration pets new
     *
     * @Route("/adoption/configuration/pets/new/{conservation_id}", name="adoption_configuration_pets_new", methods={"GET","POST"})
     * @param Request $request
     * @param ConservationsRepository $conservationsRepository
     * @return Response
     */
    public function adoption_configuration_pets_new(Request $request, ConservationsRepository $conservationsRepository): Response
    {
        $conservationPet = new ConservationPets();
        $form = $this->createForm(ConservationPetsType::class, $conservationPet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $conservation = $conservationsRepository->find($request->get('conservation_id'));
            $conservationPet->setConservation($conservation);
            $conservationPet->setDnaCheckResult(0);
            $conservationPet->setReleaseStatus(1);
            $conservationPet->setPrice(0);
            $entityManager->persist($conservationPet);
            $entityManager->flush();
            $petId = $conservationPet->getId();
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

            $dnaCheckStatus = (new DnaCheckStatus)
                ->setRegisterId($conservation->getId())
                ->setPetId($conservationPet->getId())
                ->setSiteType(AnilineConf::ANILINE_SITE_TYPE_ADOPTION);

            $entityManager->persist($petImage0);
            $entityManager->persist($petImage1);
            $entityManager->persist($petImage2);
            $entityManager->persist($petImage3);
            $entityManager->persist($petImage4);
            $entityManager->persist($conservationPet);
            $entityManager->persist($dnaCheckStatus);
            $entityManager->flush();

            return $this->redirectToRoute('adoption_pet_list');
        }

        return $this->render('animalline/adoption/configration/pets/new.twig', [
            'adoption_pet' => $conservationPet,
            'form' => $form->createView(),
        ]);
    }

    /**
     * adoption configuration pets edit
     *
     * @Route("/adoption/configuration/pets/edit/{id}", name="adoption_configuration_pets_edit", methods={"GET","POST"})
     * @param Request $request
     * @param ConservationPets $conservationPet
     * @return Response
     */
    public function adoption_configuration_pets_edit(Request $request, ConservationPets $conservationPet): Response
    {
        $form = $this->createForm(ConservationPetsType::class, $conservationPet);
        $conservationPetImage = $this->conservationPetImageRepository->findBy(
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
            foreach ($conservationPetImage as $key => $image) {
                $image->setImageUri(${'img' . $key});
                $entityManager->persist($image);
            }
            $entityManager->flush();

            return $this->redirectToRoute('adoption_pet_list');
        }

        $petImages = [];
        foreach ($conservationPetImage as $image) {
            $petImages[] = [
                'image_uri' => $image->getImageUri(),
                'sort_order' => $image->getSortOrder(),
            ];
        }

        return $this->render('animalline/adoption/configration/pets/edit.twig', [
            'adoption_pet' => $conservationPet,
            'pet_mages' => $petImages,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Copy image and retrieve new url of the copy
     *
     * @param  string $imageUrl
     * @param  int $petId
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
     * pet data by pet kind
     *
     * @Route("/pet_data_by_pet_kind", name="pet_data_by_pet_kind", methods={"GET"})
     * @param Request $request
     * @param BreedsRepository $breedsRepository
     * @param CoatColorsRepository $coatColorsRepository
     * @return JsonResponse
     */
    public function petDataByPetKind(Request $request, BreedsRepository $breedsRepository, CoatColorsRepository $coatColorsRepository)
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
     * adoption pets upload crop image
     *
     * @Route("/adoption/configration/pets/upload", name="adoption_pets_upload_crop_image", methods={"POST"}, options={"expose"=true})
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
     * adoption base information
     *
     * @Route("/adoption/configration/baseinfo", name="adoption_baseinfo")
     * @Template("/animalline/adoption/configration/baseinfo.twig")
     * @param Request $request
     * @param ConservationsRepository $conservationsRepository
     * @param PrefRepository $prefRepository
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function baseinfo(Request $request, ConservationsRepository $conservationsRepository, PrefRepository $prefRepository)
    {
        $conservation = $conservationsRepository->find($this->getUser());

        $builder = $this->formFactory->createBuilder(ConservationsType::class, $conservation);
        $form = $builder->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $addr = $request->get('conservations');
            $addr = $request->get('conservations')['addr'];
            $pref = $prefRepository->find($addr['PrefId']);
            $thumbnail_path = $request->get('thumbnail_path') ?: $conservation->getThumbnailPath();

            $conservation->setPrefId($pref)
                ->setPref($pref->getName())
                ->setCity($addr['city'])
                ->setAddress($addr['address'])
                ->setThumbnailPath($thumbnail_path);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($conservation);
            $entityManager->flush();
            return $this->redirectToRoute('adoption_configration');
        }

        return [
            'conservation' => $conservation,
            'form' => $form->createView()
        ];
    }

    /**
     * house information
     *
     * @Route("/adoption/configration/houseinfo/{pet_type}", name="adoption_houseinfo")
     * @Template("/animalline/adoption/configration/houseinfo.twig")
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function houseinfo(Request $request)
    {
        $petType = $request->get('pet_type');
        $conservationsHouse = $this->conservationsHousesRepository->findOneBy([
            'pet_type' => $petType,
            'Conservation' => $this->getUser()
        ]);
        $conservationsHouse = $conservationsHouse ?? new ConservationsHouse();
        $form = $this->createForm(ConservationHouseType::class, $conservationsHouse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $address = $request->get('conservation_house')['address'];
            $conservationsHouse->setConservation($this->getUser())
                ->setPetType($petType)
                ->setConservationHousePref($conservationsHouse->getPref()->getName())
                ->setConservationHouseCity($address['conservation_house_city'])
                ->setConservationHouseAddress($address['conservation_house_address']);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($conservationsHouse);
            $entityManager->flush();

            return $this->redirectToRoute('adoption_configration', ['pet_type' => $petType]);
        }

        return [
            'petType' => $petType,
            'form' => $form->createView(),
        ];
    }
}
