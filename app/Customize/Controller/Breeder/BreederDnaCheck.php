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
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;

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
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var FormFactoryInterface
     */
    protected $formFactory;

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
        EntityManagerInterface $entityManager,
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
        DnaCheckStatusHeaderRepository   $dnaCheckStatusHeaderRepository,
        FormFactoryInterface $formFactory
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
        $this->entityManager = $entityManager;
        $this->formFactory = $formFactory;
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
        $user = $this->getUser();

        //ブリーダーでない場合はTOPにリダイレクト
        if($user->getIsBreeder() != 1){
            return $this->redirect($this->generateUrl('breeder_top'));
        }
        
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
     * @Route("/breeder/member/dna_kit/new/{breeder_id}", name="breeder_examination_kit_new", methods={"GET","POST"})
     * @Template("animalline/breeder/member/examination_kit_form.twig")
     */
    public function breeder_examination_kit_new(Request $request,string $breeder_id = "")
    {
        if($breeder_id != ""){
            //breeder_id指定がある場合はログインユーザーチェックを行い、許可ユーザーであれば指定のブリーダーをシミュレート
            $user = $this->getUser();
            if($user->getId() == 91 || $user->getId() == 236){
                $user = $this->customerRepository->find($breeder_id);

                if(!$user){
                    throw new NotFoundHttpException();
                }
            }
            else{
                throw new NotFoundHttpException();
            }
        }
        else{
            //breeder_id指定がない場合はログイン中ユーザーとして処理
            $user = $this->getUser();
        }

        //ブリーダーでない場合はTOPにリダイレクト
        if($user->getIsBreeder() != 1){
            return $this->redirect($this->generateUrl('breeder_top'));
        }

        $isCheckStatus = false;

        $InputHeaderData = new DnaCheckStatusHeader();
        $builder = $this->formFactory->createBuilder(DnaCheckStatusHeaderType::class, $InputHeaderData);
        $breeder = $this->breedersRepository->find($user->getId());
        $breederHouseCat = $this->breederHouseRepository->findOneBy(['Breeder' => $breeder, 'pet_type' => AnilineConf::ANILINE_PET_KIND_CAT]);
        $breederHouseDog = $this->breederHouseRepository->findOneBy(['Breeder' => $breeder, 'pet_type' => AnilineConf::ANILINE_PET_KIND_DOG]);
        $DnaCheckStatusShipping = $this->dnaCheckStatusHeaderRepository->findBy(['register_id' => $breeder, 'site_type' => AnilineConf::SITE_CATEGORY_BREEDER, 'shipping_status' => AnilineConf::ANILINE_SHIPPING_STATUS_INSTRUCTING]);
        $DnaCheckStatusHeaderAccept = $this->dnaCheckStatusHeaderRepository->findBy(['register_id' => $breeder, 'site_type' => AnilineConf::SITE_CATEGORY_BREEDER, 'shipping_status' => AnilineConf::ANILINE_SHIPPING_STATUS_ACCEPT]);
        $DnaCheckStatus = array_merge($DnaCheckStatusShipping, $DnaCheckStatusHeaderAccept);
        
        $form = $builder->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //キット請求制限
            $total_kit = 0;
            foreach($DnaCheckStatus as $kit) {
                $total_kit = $total_kit + $kit->getKitUnit();
            }
            $total_kit = $total_kit + $InputHeaderData->getKitUnit();
            if($total_kit > 20){
                return $this->render('animalline/breeder/member/kit_full.twig');
            }

            $entityManager = $this->entityManager;
            $kitUnit = $InputHeaderData->getKitUnit();

            //明細登録用カウンタ
            $kits_count = $kitUnit;

            //ヘッダ追加用ループ
            for($i=1;$i<=ceil($kitUnit/5);$i++){
                $dnaCheckStatusHeader = new DnaCheckStatusHeader();
                $dnaCheckStatusHeader->setRegisterId($user->getId())
                ->setSiteType(AnilineConf::ANILINE_SITE_TYPE_BREEDER)
                ->setShippingStatus(AnilineConf::ANILINE_SHIPPING_STATUS_ACCEPT)
                ->setShippingPref($InputHeaderData->getPrefShipping())
                ->setPrefShipping($InputHeaderData->getPrefShipping())
                ->setShippingName($InputHeaderData->getShippingName())
                ->setShippingZip($InputHeaderData->getShippingZip())
                ->setShippingCity($InputHeaderData->getShippingCity())
                ->setShippingAddress($InputHeaderData->getShippingAddress())
                ->setShippingTel($InputHeaderData->getShippingTel());

                $shippingdate = new \DateTime();
                if(intval(date("H")) >= 14){
                    $shippingdate->modify('+1 days');
                }
                $dnaCheckStatusHeader->setKitShippingDate($shippingdate);
                $dnaCheckStatusHeader->setLaboType(0);

                for ($j=1;$j<=5;$j++) {
                    $Dna = (new DnaCheckStatus)
                        ->setDnaHeader($dnaCheckStatusHeader)
                        ->setSiteType(AnilineConf::ANILINE_SITE_TYPE_BREEDER);
                    $entityManager->persist($Dna);

                    //明細を全て追加したらループ脱出
                    $kits_count--;
                    if($kits_count == 0){break;}
                }
                //Breakせずにループが抜けた場合何故か6になる？
                if($j==6){$j=5;}

                $dnaCheckStatusHeader->setKitUnit($j);
                $entityManager->persist($dnaCheckStatusHeader);
            }   
            $entityManager->flush();

            if($breeder_id != ""){
                return $this->redirect($this->generateUrl('close_window'));
            }
            else{
                return $this->redirect($this->generateUrl('breeder_examination_kit'));
            }
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
