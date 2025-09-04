<?php

namespace Customize\Controller\Adoption;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Customize\Form\Type\Adoption\ConservationHouseType;
use Customize\Entity\ConservationsHouse;
use Customize\Repository\ConservationsRepository;
use Customize\Repository\ConservationsHousesRepository;
use Eccube\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;

class AdoptionHouseController extends AbstractController
{
    /**
     * @var ConservationsRepository
     */
    protected $conservationsRepository;

    /**
     * @var ConservationsHousesRepository
     */
    protected $conservationsHouseRepository;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var FormFactoryInterface
     */
    protected $formFactory;

    /**
     * ConservationController constructor.
     *
     * @param ConservationsRepository $conservationsRepository
     * @param ConservationsHousesRepository $conservationsHouseRepository
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        ConservationsRepository             $conservationsRepository,
        ConservationsHousesRepository       $conservationsHouseRepository,
        FormFactoryInterface $formFactory
    ) {
        $this->conservationsRepository = $conservationsRepository;
        $this->conservationsHouseRepository = $conservationsHouseRepository;
        $this->entityManager = $entityManager;
        $this->formFactory = $formFactory;
    }

    /**
     * 犬舎・猫舎情報編集画面
     *
     * @Route("/adoption/member/house_info/{pet_type}", name="adoption_house_info")
     * @Template("/animalline/adoption/member/house_info.twig")
     */
    public function house_info(Request $request)
    {
        //リダイレクト先設定
        $return_path = $request->get('return_path');
        if ($return_path == "") {
            $return_path = "adoption_examination";
        }

        $petType = $request->get('pet_type');
        $conservation = $this->conservationsRepository->find($this->getUser());
        $conservationsHouse = $this->conservationsHouseRepository->findOneBy(['pet_type' => $petType, 'Conservation' => $conservation]);
        if (!$conservationsHouse) {
            $conservationsHouse = new ConservationsHouse();
        }
        $builder = $this->formFactory->createBuilder(ConservationHouseType::class, $conservationsHouse);
        $conservation = $this->conservationsRepository->find($this->getUser());

        $form = $builder->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $conservationsHouse->setConservation($conservation)
                ->setPetType($petType)
                ->setConservationHousePref($conservationsHouse->getPref());
            $entityManager = $this->entityManager;
            $entityManager->persist($conservationsHouse);

            $entityManager->flush();

            return $this->redirectToRoute($return_path);
        }
        return [
            'return_path' => $return_path,
            'form' => $form->createView(),
            'petType' => $petType,
            'conservation' => $conservation,
        ];
    }
}
