<?php

namespace Customize\Controller\Breeder;

use Customize\Repository\BreederEvaluationsRepository;
use Customize\Service\BreederQueryService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Customize\Form\Type\Breeder\BreederHouseType;
use Customize\Entity\BreederHouse;
use Customize\Repository\BreederPetsRepository;
use Eccube\Repository\Master\PrefRepository;
use Customize\Repository\BreedersRepository;
use Customize\Repository\BreederHouseRepository;
use Customize\Repository\BreederExaminationInfoRepository;
use Eccube\Repository\CustomerRepository;
use Eccube\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class BreederHouseController extends AbstractController
{
    /**
     * @var BreederEvaluationsRepository
     */
    protected $breederEvaluationsRepository;

    /**
     * @var BreederQueryService
     */
    protected $breederQueryService;

    /**
     * @var BreedersRepository
     */
    protected $breedersRepository;

    /**
     * @var BreederHouse
     */
    protected $breederHouseRepository;

    /**
     * @var PrefRepository
     */
    protected $prefRepository;

    /**
     * @var BreederPetsRepository
     */
    protected $breederPetsRepository;

    /**
     * @var CustomerRepository
     */
    protected $customerRepository;

    /**
     * BreederController constructor.
     *
     * @param BreederQueryService $breederQueryService
     * @param BreedersRepository $breedersRepository
     * @param PrefRepository $prefRepository
     * @param BreederHouseRepository $breederHouseRepository
     * @param BreederPetsRepository $breederPetsRepository
     * @param BreederExaminationInfoRepository $breederExaminationInfoRepository
     * @param CustomerRepository $customerRepository
     */
    public function __construct(
        BreederQueryService              $breederQueryService,
        BreedersRepository               $breedersRepository,
        PrefRepository                   $prefRepository,
        BreederHouseRepository           $breederHouseRepository,
        BreederPetsRepository            $breederPetsRepository,
        CustomerRepository               $customerRepository
    ) {
        $this->breederQueryService = $breederQueryService;
        $this->breedersRepository = $breedersRepository;
        $this->prefRepository = $prefRepository;
        $this->breederHouseRepository = $breederHouseRepository;
        $this->breederPetsRepository = $breederPetsRepository;
        $this->customerRepository = $customerRepository;
    }

    /**
     * 犬舎・猫舎情報編集画面
     *
     * @Route("/breeder/member/house_info/{pet_type}", name="breeder_house_info")
     * @Template("/animalline/breeder/member/house_info.twig")
     */
    public function house_info(Request $request)
    {
        //リダイレクト先設定
        $return_path = $request->get('return_path');
        if ($return_path == "") {
            $return_path = "breeder_examination";
        }

        $petType = $request->get('pet_type');
        $breeder = $this->breedersRepository->find($this->getUser());
        $breederHouse = $this->breederHouseRepository->findOneBy(['pet_type' => $petType, 'Breeder' => $breeder]);
        if (!$breederHouse) {
            $breederHouse = new BreederHouse();
        }
        $builder = $this->formFactory->createBuilder(BreederHouseType::class, $breederHouse);
        $breeder = $this->breedersRepository->find($this->getUser());

        $form = $builder->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $housePref = $breederHouse->getBreederHousePrefId();
            $breederHouse->setBreeder($breeder)
                ->setPetType($petType)
                ->setBreederHousePref($housePref['name']);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($breederHouse);

            $entityManager->flush();

            return $this->redirectToRoute($return_path);
        }
        return [
            'return_path' => $return_path,
            'form' => $form->createView(),
            'petType' => $petType,
            'breeder' => $breeder,
        ];
    }
}
