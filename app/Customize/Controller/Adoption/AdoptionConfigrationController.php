<?php

namespace Customize\Controller\Adoption;

use Customize\Config\AnilineConf;
use Customize\Entity\ConservationContacts;
use Customize\Entity\ConservationPets;
use Customize\Entity\ConservationPetImage;
use Customize\Form\Type\ConservationPetsType;
use Customize\Repository\ConservationContactsRepository;
use Customize\Repository\ConservationsRepository;
use Customize\Repository\BreedsRepository;
use Customize\Repository\CoatColorsRepository;
use Customize\Repository\ConservationPetImageRepository;
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
     * @var ConservationPetImageRepository
     */
    protected $conservationPetImageRepository;

    /**
     * AdoptionConfigrationController constructor.
     */
    public function __construct(
        ConservationContactsRepository $conservationContactsRepository,
        ConservationPetImageRepository $conservationPetImageRepository
    ) {
        $this->conservationContactsRepository = $conservationContactsRepository;
        $this->conservationPetImageRepository = $conservationPetImageRepository;

    }

    /**
     * 保護団体管理ページTOP
     *
     * @Route("/adoption/configration/", name="adoption_configration")
     * @Template("animalline/adoption/configration/index.twig")
     */
    public function adoption_configration(Request $request)
    {
        $rootMessages = $this->conservationContactsRepository->findBy(
            [
                'parent_message_id' => AnilineConf::ROOT_MESSAGE_ID,
                'Conservation' => $this->getUser()
            ],
            ['is_response' => 'ASC', 'send_date' => 'DESC']
        );

        $lastReplies = [];
        foreach ($rootMessages as $message) {
            $lastReply = $this->conservationContactsRepository->findOneBy(
                ['parent_message_id' => $message->getId()],
                ['send_date' => 'DESC']
            );
            $lastReplies[$message->getId()] = $lastReply ? $lastReply->getSendDate() : null;
        }

        return $this->render(
            'animalline/adoption/configration/index.twig',
            [
                'rootMessages' => $rootMessages,
                'lastReplies' => $lastReplies,
                'conservation' => $this->getUser()
            ]
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
        $rootMessage = $this->conservationContactsRepository->find($contact_id);
        if (!$rootMessage) {
            throw new HttpException\NotFoundHttpException();
        }

        $description = $request->get('contact_description');

        $conservationContact = new ConservationContacts();
        $form = $this->createFormBuilder($conservationContact)->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $conservationContact->setCustomer($rootMessage->getCustomer())
                ->setConservation($this->getUser())
                ->setMessageFrom(AnilineConf::MESSAGE_FROM_CONFIGURATION)
                ->setPet($rootMessage->getPet())
                ->setContactType(AnilineConf::CONTACT_TYPE_REPLY)
                ->setContactDescription($description)
                ->setParentMessageId($contact_id)
                ->setSendDate(new DateTime())
                ->setIsResponse(AnilineConf::RESPONSE_REPLIED)
                ->setContractStatus(AnilineConf::CONTRACT_STATUS_UNDER_NEGOTIATION)
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

    /**
     * @Route("/adoption/configuration/pets/new/{conservation_id}", name="adoption_configuration_pets_new", methods={"GET","POST"})
     */
    public function adoption_configuration_pets_new(Request $request, ConservationsRepository $conservationsRepository): Response
    {
        $conservationPet = new ConservationPets();
        $form = $this->createForm(ConservationPetsType::class, $conservationPet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $petImage0 = (new ConservationPetImage())
                ->setImageType(AnilineConf::PET_PHOTO_TYPE_IMAGE)->setImageUri($request->get('img0'))->setSortOrder(1)->setConservationPetId($conservationPet);
            $petImage1 = (new ConservationPetImage())
                ->setImageType(AnilineConf::PET_PHOTO_TYPE_IMAGE)->setImageUri($request->get('img1'))->setSortOrder(2)->setConservationPetId($conservationPet);
            $petImage2 = (new ConservationPetImage())
                ->setImageType(AnilineConf::PET_PHOTO_TYPE_IMAGE)->setImageUri($request->get('img2'))->setSortOrder(3)->setConservationPetId($conservationPet);
            $petImage3 = (new ConservationPetImage())
                ->setImageType(AnilineConf::PET_PHOTO_TYPE_IMAGE)->setImageUri($request->get('img3'))->setSortOrder(4)->setConservationPetId($conservationPet);
            $petImage4 = (new ConservationPetImage())
                ->setImageType(AnilineConf::PET_PHOTO_TYPE_IMAGE)->setImageUri($request->get('img4'))->setSortOrder(5)->setConservationPetId($conservationPet);
            $conservationPet->addConservationPetImage($petImage0);
            $conservationPet->addConservationPetImage($petImage1);
            $conservationPet->addConservationPetImage($petImage2);
            $conservationPet->addConservationPetImage($petImage3);
            $conservationPet->addConservationPetImage($petImage4);
            $conservationPet->setThumbnailPath($request->get('img0'));

            $conservation = $conservationsRepository->find($request->get('conservation_id'));
            $conservationPet->setConservationId($conservation);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($conservationPet);
            $entityManager->persist($petImage0);
            $entityManager->persist($petImage1);
            $entityManager->persist($petImage2);
            $entityManager->persist($petImage3);
            $entityManager->persist($petImage4);
            $entityManager->flush();

            return $this->redirectToRoute('adoption_configration');
        }

        return $this->render('animalline/adoption/configration/pets/new.twig', [
            'adoption_pet' => $conservationPet,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/adoption/configuration/pets/edit/{id}", name="adoption_configuration_pets_edit", methods={"GET","POST"})
     */
    public function adoption_configuration_pets_edit(Request $request, ConservationPets $conservationPet): Response
    {
        $form = $this->createForm(ConservationPetsType::class, $conservationPet);
        $conservationPetImage = $this->conservationPetImageRepository->findBy(
            ['conservation_pet_id'=> $conservationPet->getId()],
            ['sort_order' => 'ASC']
        );
        $request->request->set('thumbnail_path', $request->get('img0'));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $conservationPet->setThumbnailPath($request->get('img0'));
            $entityManager->persist($conservationPet);
            foreach($conservationPetImage as $key => $image) {
                $image->setImageUri($request->get('img' . $key));
                $entityManager->persist($image);
            }
            $entityManager->flush();

            return $this->redirectToRoute('adoption_configration');
        }

        $petImages = []; 
        foreach($conservationPetImage as $image) {
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
     * @Route("/pet_data_by_pet_kind", name="pet_data_by_pet_kind", methods={"GET"})
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
}
