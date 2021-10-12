<?php

namespace Customize\Controller\Adoption;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Customize\Form\Type\ConservationHouseType;
use Customize\Entity\ConservationsHouse;
use Customize\Repository\ConservationsRepository;
use Customize\Repository\ConservationsHousesRepository;
use Eccube\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

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
     * ConservationController constructor.
     *
     * @param ConservationsRepository $conservationsRepository
     * @param ConservationsHousesRepository $conservationsHouseRepository
     */
    public function __construct(
        ConservationsRepository             $conservationsRepository,
        ConservationsHousesRepository       $conservationsHouseRepository
    ) {
        $this->conservationsRepository = $conservationsRepository;
        $this->conservationsHouseRepository = $conservationsHouseRepository;
    }

    /**
     * 犬舎・猫舎情報編集画面
     *
     * @Route("/adoption/member/house_info/{pet_type}", name="adoption_house_info")
     * @Template("/animalline/adoption/member/house_info.twig")
     */
    public function house_info(Request $request)
    {
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
            $address = $request->get('conservation_house')['address'];
            $conservationsHouse->setConservation($conservation)
                ->setPetType($petType)
                ->setConservationHousePref($conservationsHouse->getPref()->getName())
                ->setConservationHouseCity($address['conservation_house_city'])
                ->setConservationHouseAddress($address['conservation_house_address']);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($conservationsHouse);
            $entityManager->flush();

            return $this->redirectToRoute('adoption_examination');
        }
        return [
            'form' => $form->createView(),
            'petType' => $petType,
            'conservation' => $conservation,
        ];
    }
}