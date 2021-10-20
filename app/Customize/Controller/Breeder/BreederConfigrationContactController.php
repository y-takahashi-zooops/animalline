<?php

namespace Customize\Controller\Breeder;

use Carbon\Carbon;
use Customize\Config\AnilineConf;
use Customize\Entity\BreederContacts;
use Customize\Repository\BreederContactHeaderRepository;
use Customize\Repository\BreederContactsRepository;
use Customize\Repository\SendoffReasonRepository;
use Eccube\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception as HttpException;
use Symfony\Component\Routing\Annotation\Route;
use DateTime;

class BreederConfigrationContactController extends AbstractController
{
    /**
     * @var BreederContactsRepository
     */
    protected $breederContactsRepository;

    /**
     * @var BreederContactHeaderRepository
     */
    protected $breederContactHeaderRepository;

    /**
     * @var SendoffReasonRepository
     */
    protected $sendoffReasonRepository;

    /**
     * BreederConfigrationController constructor.
     * @param BreederContactsRepository $breederContactsRepository
     * @param BreederContactHeaderRepository $breederContactHeaderRepository
     * @param SendoffReasonRepository $sendoffReasonRepository
     */
    public function __construct(
        BreederContactsRepository        $breederContactsRepository,
        BreederContactHeaderRepository   $breederContactHeaderRepository,
        SendoffReasonRepository          $sendoffReasonRepository
    ) {
        $this->breederContactsRepository = $breederContactsRepository;
        $this->breederContactHeaderRepository = $breederContactHeaderRepository;
        $this->sendoffReasonRepository = $sendoffReasonRepository;
    }

    /**
     * Page breeder's message
     *
     * @Route("/breeder/configration/message/{contact_id}", name="breeder_configration_messages", requirements={"contact_id" = "\d+"})
     * @Template("animalline/breeder/configration/message.twig")
     */
    public function breeder_configration_message(Request $request, $contact_id)
    {
        $isScroll = false;
        $isAcceptContract = $request->get('accept-contract');
        $reasonCancel = $request->get('reason');
        $replyMessage = $request->get('reply_message');
        $rootMessage = $this->breederContactHeaderRepository->find($contact_id);
        if (!$rootMessage) {
            throw new HttpException\NotFoundHttpException();
        }
        $rootMessage->setBreederNewMsg(0);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($rootMessage);
        $entityManager->flush();
        $description = $request->get('reply_message');

        $breederContact = new BreederContacts();

        if ($replyMessage) {
            $breederContact->setBreederHeader($rootMessage)
                ->setMessageFrom(AnilineConf::MESSAGE_FROM_MEMBER)
                ->setContactDescription($description)
                ->setSendDate(new DateTime());
            $entityManager = $this->getDoctrine()->getManager();
            $rootMessage->setCustomerNewMsg(1)
                ->setLastMessageDate(Carbon::now());
            $entityManager->persist($breederContact);
            $entityManager->persist($rootMessage);
            $entityManager->flush();
            $isScroll = true;
        }

        if ($isAcceptContract) {
            if ($rootMessage->getContractStatus() == AnilineConf::CONTRACT_STATUS_UNDER_NEGOTIATION) {
                $rootMessage->setContractStatus(AnilineConf::CONTRACT_STATUS_WAITCONTRACT)
                    ->setBreederCheck(1);
            }
            if ($rootMessage->getContractStatus() == AnilineConf::CONTRACT_STATUS_WAITCONTRACT && $rootMessage->getCustomerCheck() == 1) {
                $rootMessage->setContractStatus(AnilineConf::CONTRACT_STATUS_CONTRACT)
                    ->setBreederCheck(1);
            }
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($rootMessage);
            $entityManager->flush();

            return $this->redirectToRoute('breeder_configration_messages', ['contact_id' => $rootMessage->getId()]);
        }

        if ($reasonCancel) {
            $rootMessage->setContractStatus(AnilineConf::CONTRACT_STATUS_NONCONTRACT)
                ->setCustomerNewMsg(1)
                ->setSendoffReason($reasonCancel);

            $breederContact = (new BreederContacts())
                ->setMessageFrom(AnilineConf::MESSAGE_FROM_MEMBER)
                ->setContactDescription('今回の取引非成立となりました')
                ->setSendDate(Carbon::now())
                ->setBreederHeader($rootMessage);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($rootMessage);
            $entityManager->persist($breederContact);
            $entityManager->flush();

            return $this->redirectToRoute('get_message_breeder_configration');
        }

        $messages = $this->breederContactsRepository->findBy(
            ['BreederHeader' => $contact_id],
            ['send_date' => 'ASC']
        );
        $reasons = $this->sendoffReasonRepository->findBy(['is_breeder_visible' => AnilineConf::BREEDER_VISIBLE_SHOW]);

        return $this->render('animalline/breeder/configration/message.twig', [
            'rootMessage' => $rootMessage,
            'messages' => $messages,
            'reasons' => $reasons,
            'isScroll' => $isScroll
        ]);
    }
}
