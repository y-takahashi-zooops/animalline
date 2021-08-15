<?php

namespace Customize\Controller\Adoption;

use Carbon\Carbon;
use Customize\Config\AnilineConf;
use Customize\Entity\ConservationContacts;
use Customize\Repository\ConservationContactsRepository;
use Customize\Repository\ConservationPetsRepository;
use Eccube\Controller\AbstractController;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception as HttpException;
use Symfony\Component\Routing\Annotation\Route;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Customize\Form\Type\ConservationContactType;
use DateTime;

class AdoptionConfigrationController extends AbstractController
{
    /**
     * AdoptionConfigrationController constructor.
     */
    public function __construct(
        ConservationContactsRepository $conservationContactsRepository
    )
    {
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

}
