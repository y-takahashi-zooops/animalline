<?php

namespace Customize\Controller\Breeder;

use Customize\Config\AnilineConf;
use Customize\Entity\DnaCheckStatusHeader;
use Customize\Form\Type\Front\DnaCheckStatusHeaderType;
use Customize\Repository\BreederEvaluationsRepository;
use Customize\Service\BreederQueryService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Customize\Entity\BreederHouse;
use Customize\Entity\DnaCheckStatus;
use Customize\Repository\BreederPetsRepository;
use Customize\Repository\PetsFavoriteRepository;
use Eccube\Repository\Master\PrefRepository;
use Customize\Repository\BreederContactsRepository;
use Customize\Repository\BreedersRepository;
use Customize\Repository\BreederHouseRepository;
use Customize\Repository\BreederExaminationInfoRepository;
use Customize\Repository\DnaCheckStatusRepository;
use Customize\Service\DnaQueryService;
use Eccube\Repository\CustomerRepository;
use Eccube\Controller\AbstractController;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Customize\Repository\DnaCheckStatusHeaderRepository;

class BreederDnaCheck extends AbstractController
{
    /**
     * @var BreederEvaluationsRepository
     */
    protected $breederEvaluationsRepository;

    /**
     * @var BreederContactsRepository
     */
    protected $breederContactsRepository;

    /**
     * @var BreederQueryService
     */
    protected $breederQueryService;

    /**
     * @var PetsFavoriteRepository
     */
    protected $petsFavoriteRepository;

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
     * @var BreederExaminationInfoRepository
     */
    protected $breederExaminationInfoRepository;

    /**
     * @var CustomerRepository
     */
    protected $customerRepository;

    /**
     * @var DnaQueryService
     */
    protected $dnaQueryService;

    /**
     * @var DnaCheckStatusRepository
     */
    protected $dnaCheckStatusRepository;

    /**
     * @var DnaCheckStatusHeaderRepository
     */
    protected $dnaCheckStatusHeaderRepository;

    /**
     * BreederController constructor.
     *
     * @param BreederContactsRepository $breederContactsRepository
     * @param BreederQueryService $breederQueryService
     * @param PetsFavoriteRepository $petsFavoriteRepository
     * @param BreedersRepository $breedersRepository
     * @param PrefRepository $prefRepository
     * @param BreederHouseRepository $breederHouseRepository
     * @param BreederPetsRepository $breederPetsRepository
     * @param BreederExaminationInfoRepository $breederExaminationInfoRepository
     * @param CustomerRepository $customerRepository
     * @param DnaQueryService $dnaQueryService
     * @param DnaCheckStatusRepository $dnaCheckStatusRepository
     * @param DnaCheckStatusHeaderRepository $dnaCheckStatusHeaderRepository
     */
    public function __construct(
        BreederContactsRepository        $breederContactsRepository,
        BreederQueryService              $breederQueryService,
        PetsFavoriteRepository           $petsFavoriteRepository,
        BreedersRepository               $breedersRepository,
        PrefRepository                   $prefRepository,
        BreederHouseRepository           $breederHouseRepository,
        BreederPetsRepository            $breederPetsRepository,
        BreederExaminationInfoRepository $breederExaminationInfoRepository,
        CustomerRepository               $customerRepository,
        BreederEvaluationsRepository     $breederEvaluationsRepository,
        DnaQueryService                  $dnaQueryService,
        DnaCheckStatusRepository         $dnaCheckStatusRepository,
        DnaCheckStatusHeaderRepository   $dnaCheckStatusHeaderRepository
    ) {
        $this->breederContactsRepository = $breederContactsRepository;
        $this->breederQueryService = $breederQueryService;
        $this->petsFavoriteRepository = $petsFavoriteRepository;
        $this->breedersRepository = $breedersRepository;
        $this->prefRepository = $prefRepository;
        $this->breederHouseRepository = $breederHouseRepository;
        $this->breederPetsRepository = $breederPetsRepository;
        $this->breederExaminationInfoRepository = $breederExaminationInfoRepository;
        $this->customerRepository = $customerRepository;
        $this->breederEvaluationsRepository = $breederEvaluationsRepository;
        $this->dnaQueryService = $dnaQueryService;
        $this->dnaCheckStatusRepository = $dnaCheckStatusRepository;
        $this->dnaCheckStatusHeaderRepository = $dnaCheckStatusHeaderRepository;
    }

    /**
     *
     * 検査キット請求
     *
     * @Route("/breeder/member/dna_kit", name="breeder_examination_kit", methods={"GET","POST"})
     * @Template("animalline/breeder/member/examination_kit_list.twig")
     */
    public function breeder_examination_kit(Request $request, PaginatorInterface $paginator)
    {
        $isAll = $request->get('is_all') ?? false;
        $registerId = $this->getUser();

        $dnas = $this->dnaCheckStatusHeaderRepository->createQueryBuilder('dna')
            ->where('dna.register_id = :register_id')
            ->andWhere('dna.site_type = :site_type')
            ->setParameters([':register_id' => $registerId, ':site_type' => AnilineConf::ANILINE_SITE_TYPE_BREEDER])
            ->select('dna.id as id, dna.kit_unit, dna.shipping_status, dna.kit_shipping_date');
        if (!$isAll) {
            $dnas->andWhere($dnas->expr()->notIn('dna.shipping_status', AnilineConf::ANILINE_SHIPPING_STATUS_SHIPPED));
        }

        $dnas->orderBy('dna.create_date', 'DESC')
            ->getQuery()
            ->getResult();

        $dnas = $paginator->paginate(
            $dnas,
            $request->query->getInt('page', 1),
            $request->query->getInt('item', AnilineConf::ANILINE_NUMBER_ITEM_PER_PAGE)
        );

        return compact('dnas');
    }

    /**
     *
     * 検査キット請求
     *
     * @Route("/breeder/member/dna_kit/info", name="breeder_examination_kit_info", methods={"GET","POST"})
     * @Template("animalline/breeder/member/examination_kit_info.twig")
     */
    public function breeder_examination_kit_info(Request $request)
    {
        return[];
    }
    /**
     *
     * 検査キット請求
     *
     * @Route("/breeder/member/dna_kit/new", name="breeder_examination_kit_new", methods={"GET","POST"})
     * @Template("animalline/breeder/member/examination_kit_form.twig")
     */
    public function breeder_examination_kit_new(Request $request)
    {
        $isCheckStatus = false;
        $dnaCheckStatusHeader = new DnaCheckStatusHeader();
        $builder = $this->formFactory->createBuilder(DnaCheckStatusHeaderType::class, $dnaCheckStatusHeader);
        $breeder = $this->breedersRepository->find($this->getUser()->getId());
        $breederHouseCat = $this->breederHouseRepository->findOneBy(['Breeder' => $breeder, 'pet_type' => AnilineConf::ANILINE_PET_KIND_CAT]);
        $breederHouseDog = $this->breederHouseRepository->findOneBy(['Breeder' => $breeder, 'pet_type' => AnilineConf::ANILINE_PET_KIND_DOG]);
        $DnaCheckStatusShipping = $this->dnaCheckStatusHeaderRepository->findBy(['register_id' => $breeder, 'site_type' => AnilineConf::SITE_CATEGORY_BREEDER, 'shipping_status' => AnilineConf::ANILINE_SHIPPING_STATUS_INSTRUCTING]);
        $DnaCheckStatusHeaderAccept = $this->dnaCheckStatusHeaderRepository->findBy(['register_id' => $breeder, 'site_type' => AnilineConf::SITE_CATEGORY_BREEDER, 'shipping_status' => AnilineConf::ANILINE_SHIPPING_STATUS_ACCEPT]);
        $DnaCheckStatus = array_merge($DnaCheckStatusShipping, $DnaCheckStatusHeaderAccept);
        
        $form = $builder->getForm();
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $dnaCheckStatusHeader->setRegisterId($this->getUser()->getId())
                ->setSiteType(AnilineConf::ANILINE_SITE_TYPE_BREEDER)
                ->setShippingStatus(AnilineConf::ANILINE_SHIPPING_STATUS_ACCEPT)
                ->setShippingPref($dnaCheckStatusHeader->getPrefShipping());

            $shippingdate = new \DateTime();
            if(intval(date("H")) >= 14){
                $shippingdate->modify('+1 days');
            }

            $dnaCheckStatusHeader->setKitShippingDate($shippingdate);
            $dnaCheckStatusHeader->setLaboType(0);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($dnaCheckStatusHeader);
            $entityManager->flush();

            $kitUnit = $dnaCheckStatusHeader->getKitUnit();
            for ($i = 0; $i < $kitUnit; $i++) {
                $Dna = (new DnaCheckStatus)
                    ->setDnaHeader($dnaCheckStatusHeader)
                    ->setSiteType(AnilineConf::ANILINE_SITE_TYPE_BREEDER);
                $entityManager->persist($Dna);
            }
            $entityManager->flush();
            return $this->redirect($this->generateUrl('breeder_examination_kit'));
        }

        //キット請求制限
        if (count($DnaCheckStatus) == 4) {
            return $this->render('animalline/breeder/member/kit_full.twig');
        }

        $formData = $request->request->get('dna_check_status_header');
        //dump($formData);
        $param = $formData;
        return [
            'request' => $param,
            'form' => $form->createView(),
            'breeder' => $breeder,
            'breederHouseCat' => $breederHouseCat,
            'breederHouseDog' => $breederHouseDog
        ];
    }
}
