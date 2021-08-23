<?php

namespace Customize\Controller\Breeder;

use Customize\Config\AnilineConf;
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
     * ブリーダー管理ページTOP
     *
     * @Route("/breeder/configuration/", name="breeder_configration")
     * @Template("animalline/breeder/configuration/index.twig")
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
        $pets = $this->breederPetsRepository->findBy(['breeder' => $breederId], ['update_date' => 'DESC']);

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
     * @Route("/breeder/configuration/pets/new/{breeder_id}", name="breeder_configuration_pets_new", methods={"GET","POST"})
     */
    public function breeder_configuration_pets_new(Request $request)
    {
        return;
    }

    /**
     * @Route("/breeder/configuration/pets/edit/{id}", name="breeder_configuration_pets_edit", methods={"GET","POST"})
     */
    public function breeder_configuration_pets_edit(Request $request)
    {
        return;
    }
}