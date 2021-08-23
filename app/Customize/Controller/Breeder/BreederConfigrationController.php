<?php

namespace Customize\Controller\Breeder;

use Customize\Config\AnilineConf;
use Customize\Entity\BreederContacts;
use Customize\Repository\BreederContactsRepository;
use Customize\Repository\BreederPetsRepository;
use Eccube\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception as HttpException;
use Symfony\Component\Routing\Annotation\Route;
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
     * BreederConfigrationController constructor.
     * @param BreederContactsRepository $breederContactsRepository
     * @param BreederPetsRepository $breederPetsRepository
     */
    public function __construct(
        BreederContactsRepository $breederContactsRepository,
        BreederPetsRepository $breederPetsRepository
    )
    {
        $this->breederContactsRepository = $breederContactsRepository;
        $this->breederPetsRepository = $breederPetsRepository;
    }

    /**
     * @Route("/breeder/configration/all_message", name=" get_message_breeder_configration")
     * @Template("animalline/breeder/configration/get_message.twig")
     */
    public function get_message_breeder_configration(Request $request)
    {
        $rootMessages = $this->breederContactsRepository->findBy(
            [
                'parent_message_id' => AnilineConf::ROOT_MESSAGE_ID,
                'breeder' => $this->getUser()
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

        $breederId = $this->getUser()->getId();
        $pets = $this->breederPetsRepository->findBy(['breeder' => $breederId], ['update_date' => 'DESC']);

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
     * ブリーダー管理ページTOP
     *
     * @Route("/breeder/configration/", name="breeder_configration")
     * @Template("animalline/breeder/configration/index.twig")
     */
    public function breeder_configration(Request $request)
    {
        $rootMessages = $this->breederContactsRepository->findBy(
            [
                'parent_message_id' => AnilineConf::ROOT_MESSAGE_ID,
                'breeder' => $this->getUser()
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

        $breederId = $this->getUser()->getId();
        $pets = $this->breederPetsRepository->findBy(['breeder_id' => $breederId], ['update_date' => 'DESC']);

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
    public function breeder_configration_pets_new(Request $request)
    {
        return;
    }

    /**
     * @Route("/breeder/configration/pets/edit/{id}", name="breeder_configuration_pets_edit", methods={"GET","POST"})
     */
    public function breeder_configration_pets_edit(Request $request)
    {
        return;
    }
}