<?php

namespace Customize\Controller\Adoption;

use Carbon\Carbon;
use Customize\Config\AnilineConf;
use Customize\Entity\ConservationContacts;
use Customize\Repository\ConservationContactHeaderRepository;
use Customize\Repository\ConservationContactsRepository;
use Customize\Repository\ConservationPetsRepository;
use Customize\Repository\SendoffReasonRepository;
use Eccube\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception as HttpException;
use Symfony\Component\Routing\Annotation\Route;
use DateTime;

class AdoptionConfigrationContactController extends AbstractController
{
    /**
     * @var ConservationContactsRepository
     */
    protected $conservationContactsRepository;

    /**
     * @var ConservationContactHeaderRepository
     */
    protected $conservationContactHeaderRepository;

    /**
     * @var SendoffReasonRepository
     */
    protected $sendoffReasonRepository;

    /**
     * @var ConservationPetsRepository
     */
    protected $conservationPetsRepository;

    /**
     * BreederConfigrationController constructor.
     * @param ConservationContactsRepository $conservationContactsRepository
     * @param ConservationContactHeaderRepository $conservationContactHeaderRepository
     * @param SendoffReasonRepository $sendoffReasonRepository
     * @param ConservationPetsRepository     $conservationPetsRepository
     */
    public function __construct(
        ConservationContactsRepository      $conservationContactsRepository,
        ConservationContactHeaderRepository $conservationContactHeaderRepository,
        SendoffReasonRepository             $sendoffReasonRepository,
        ConservationPetsRepository     $conservationPetsRepository
    )
    {
        $this->conservationContactsRepository = $conservationContactsRepository;
        $this->conservationContactHeaderRepository = $conservationContactHeaderRepository;
        $this->sendoffReasonRepository = $sendoffReasonRepository;
        $this->conservationPetsRepository = $conservationPetsRepository;;
    }

    /**
     * Page All message's adoption
     *
     * @Route("/adoption/configration/all_message", name="get_message_adoption_configration")
     * @Template("animalline/adoption/configration/get_message.twig")
     */
    public function get_message_adoption_configration(Request $request)
    {
        $rootMessages = $this->conservationContactHeaderRepository->findBy(
            [
                'Conservation' => $this->getUser()
            ],
            ['send_date' => 'DESC']
        );

        $lastReplies = [];
        $name = [];
        foreach ($rootMessages as $message) {
            $name[$message->getId()] = "{$message->getCustomer()->getName01()} {$message->getCustomer()->getName02()}";
        }

        $pets = $this->conservationPetsRepository->findBy(['Conservation' => $this->getUser()], ['update_date' => 'DESC']);

        return $this->render(
            'animalline/adoption/configration/get_message.twig',
            [
                'rootMessages' => $rootMessages,
                'name' => $name,
                'conservation' => $this->getUser(),
                'pets' => $pets,
            ]
        );
    }

    /**
     * Page adoption's message
     *
     * @Route("/adoption/configration/message/{contact_id}", name="adoption_configration_messages", requirements={"contact_id" = "\d+"})
     * @Template("animalline/adoption/configration/message.twig")
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
            if ($rootMessage->getContractStatus() == AnilineConf::CONTRACT_STATUS_UNDER_NEGOTIATION) {
                $rootMessage->setContractStatus(AnilineConf::CONTRACT_STATUS_WAITCONTRACT)
                    ->setConservationCheck(1);
            }
            if ($rootMessage->getContractStatus() == AnilineConf::CONTRACT_STATUS_WAITCONTRACT && $rootMessage->getCustomerCheck() == 1) {
                $rootMessage->setContractStatus(AnilineConf::CONTRACT_STATUS_CONTRACT)
                    ->setConservationCheck(1);
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

            $breederContact = (new ConservationContacts())
                ->setMessageFrom(AnilineConf::MESSAGE_FROM_CONFIGURATION)
                ->setContactDescription('今回の取引非成立となりました')
                ->setSendDate(Carbon::now())
                ->setConservationHeader($rootMessage);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($rootMessage);
            $entityManager->persist($breederContact);
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
}
