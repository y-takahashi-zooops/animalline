<?php

namespace Customize\Controller\Adoption;

use Customize\Config\AnilineConf;
use Customize\Entity\ConservationContacts;
use Customize\Entity\ConservationPets;
use Customize\Form\Type\ConservationPetsType;
use Customize\Repository\ConservationContactsRepository;
use Customize\Repository\ConservationsRepository;
use Customize\Repository\BreedsRepository;
use Customize\Repository\CoatColorsRepository;
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
     * AdoptionConfigrationController constructor.
     */
    public function __construct(
        ConservationContactsRepository $conservationContactsRepository
    ) {
        $this->conservationContactsRepository = $conservationContactsRepository;
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
            $conservation = $conservationsRepository->find($request->get('conservation_id'));
            $conservationPet->setConservationId($conservation);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($conservationPet);
            $entityManager->flush();

            return $this->redirectToRoute('adoption_configuration_pets_edit', ['id' => $conservationPet->getID()]);
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
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('adoption_configration');
        }

        return $this->render('animalline/adoption/configration/pets/edit.twig', [
            'adoption_pet' => $conservationPet,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/by_pet_kind", name="by_pet_kind", methods={"GET"})
     */
    public function byPetKind(Request $request, BreedsRepository $breedsRepository, CoatColorsRepository $coatColorsRepository)
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
}
