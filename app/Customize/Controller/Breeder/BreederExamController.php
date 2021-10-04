<?php

namespace Customize\Controller\Breeder;

use Customize\Config\AnilineConf;
use Customize\Repository\BreederContactHeaderRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Customize\Form\Type\BreederExaminationInfoType;
use Customize\Entity\BreederHouse;
use Customize\Entity\BreederExaminationInfo;
use Customize\Repository\BreederPetsRepository;
use Customize\Repository\PetsFavoriteRepository;
use Eccube\Repository\Master\PrefRepository;
use Customize\Repository\SendoffReasonRepository;
use Customize\Repository\BreedersRepository;
use Customize\Repository\BreederHouseRepository;
use Customize\Repository\BreederExaminationInfoRepository;
use Customize\Repository\BreederPetImageRepository;
use Customize\Repository\DnaCheckStatusRepository;
use Customize\Service\DnaQueryService;
use Eccube\Repository\CustomerRepository;
use Eccube\Controller\AbstractController;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception as HttpException;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Customize\Repository\DnaCheckStatusHeaderRepository;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Eccube\Form\Type\Front\CustomerLoginType;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BreederExamController extends AbstractController
{

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
     * @var DnaQueryService;
     */
    protected $dnaQueryService;

    /**
     * @var DnaCheckStatusRepository;
     */
    protected $dnaCheckStatusRepository;

    /**
     * @var DnaCheckStatusHeaderRepository;
     */
    protected $dnaCheckStatusHeaderRepository;

    /**
     * BreederController constructor.
     *
     * @param SendoffReasonRepository $sendoffReasonRepository
     * @param BreedersRepository $breedersRepository
     * @param PrefRepository $prefRepository
     * @param BreederHouseRepository $breederHouseRepository
     * @param BreederPetsRepository $breederPetsRepository
     * @param BreederExaminationInfoRepository $breederExaminationInfoRepository
     * @param DnaQueryService $dnaQueryService
     * @param DnaCheckStatusRepository $dnaCheckStatusRepository
     * @param DnaCheckStatusHeaderRepository $dnaCheckStatusHeaderRepository
     */
    public function __construct(
        PetsFavoriteRepository           $petsFavoriteRepository,
        SendoffReasonRepository          $sendoffReasonRepository,
        BreedersRepository               $breedersRepository,
        PrefRepository                   $prefRepository,
        BreederHouseRepository           $breederHouseRepository,
        BreederPetsRepository            $breederPetsRepository,
        BreederExaminationInfoRepository $breederExaminationInfoRepository,
        BreederContactHeaderRepository   $breederContactHeaderRepository,
        DnaQueryService                  $dnaQueryService,
        DnaCheckStatusRepository         $dnaCheckStatusRepository,
        DnaCheckStatusHeaderRepository   $dnaCheckStatusHeaderRepository
    ) {
        $this->petsFavoriteRepository = $petsFavoriteRepository;
        $this->sendoffReasonRepository = $sendoffReasonRepository;
        $this->breedersRepository = $breedersRepository;
        $this->prefRepository = $prefRepository;
        $this->breederHouseRepository = $breederHouseRepository;
        $this->breederPetsRepository = $breederPetsRepository;
        $this->breederExaminationInfoRepository = $breederExaminationInfoRepository;
        $this->breederContactHeaderRepository = $breederContactHeaderRepository;
        $this->dnaQueryService = $dnaQueryService;
        $this->dnaCheckStatusRepository = $dnaCheckStatusRepository;
        $this->dnaCheckStatusHeaderRepository = $dnaCheckStatusHeaderRepository;
    }

    /**
     * ブリーダー登録申請画面
     *
     * @Route("/breeder/member/examination", name="breeder_examination")
     * @Template("animalline/breeder/member/examination.twig")
     */
    public function examination(Request $request)
    {
        $user = $this->getUser();
        $breeder = $this->breedersRepository->find($user);

        $step = 1;

        // 基本情報が登録済みであればSTEP2を表示
        if ($breeder) {
            $step = 2;

            // 基本情報の取扱ペットに対応する犬舎・猫舎情報が登録されていればSTEP3を表示
            $handling_pet_kind = $breeder->getHandlingPetKind();
            $dog_house_info = $this->breederHouseRepository->findOneBy(["Breeder" => $breeder, "pet_type" => 1]);
            $cat_house_info = $this->breederHouseRepository->findOneBy(["Breeder" => $breeder, "pet_type" => 2]);

            if ($handling_pet_kind == 0 && $cat_house_info && $dog_house_info) {
                $step = 3;
            }
            if ($handling_pet_kind == 1 && $dog_house_info) {
                $step = 3;
            }
            if ($handling_pet_kind == 2 && $cat_house_info) {
                $step = 3;
            }
            // 審査情報が登録されていればSTEP4を表示
            $dog_examination_info = $this->breederExaminationInfoRepository->findOneBy(["Breeder" => $breeder, "pet_type" => 1]);
            $cat_examination_info = $this->breederExaminationInfoRepository->findOneBy(["Breeder" => $breeder, "pet_type" => 2]);

            if ($handling_pet_kind == 0 && $dog_examination_info && $cat_examination_info) {
                $step = 4;
            }
            if ($handling_pet_kind == 1 && $dog_examination_info) {
                $step = 4;
            }
            if ($handling_pet_kind == 2 && $cat_examination_info) {
                $step = 4;
            }

            // 審査申請済であればSTEP5として審査中メッセージ
            $examination_status = $breeder->getExaminationStatus();
            if ($examination_status == 1) {
                $step = 5;
            }

            // 審査結果が出ていれば審査結果を表示
        }
        return $this->render('animalline/breeder/member/examination.twig', [
            'user' => $user,
            'breeder' => $breeder,
            'step' => $step,
        ]);
    }

    /**
     * 審査情報編集画面
     *
     * @Route("/breeder/member/examination_info/{pet_type}", name="breeder_examination_info", methods={"GET","POST"})
     * @Template("/animalline/breeder/member/examination_info.twig")
     */
    public function examination_info(Request $request)
    {
        $petType = $request->get('pet_type');
        $breeder = $this->breedersRepository->find($this->getUser());
        $breederExaminationInfo = $this->breederExaminationInfoRepository->findOneBy([
            'Breeder' => $breeder,
            'pet_type' => $petType
        ]);
        $isEdit = false;
        if ($breederExaminationInfo) {
            $isEdit = true;
            if (in_array(
                $breederExaminationInfo->getPedigreeOrganization(),
                [AnilineConf::PEDIGREE_ORGANIZATION_JKC, AnilineConf::PEDIGREE_ORGANIZATION_KC]
            )) {
                $breederExaminationInfo->setGroupOrganization($breederExaminationInfo->getPedigreeOrganization());
                $breederExaminationInfo->setPedigreeOrganization(AnilineConf::PEDIGREE_ORGANIZATION_JKC);
            }
        } else {
            $breederExaminationInfo = new BreederExaminationInfo();
        }

        $form = $this->createForm(BreederExaminationInfoType::class, $breederExaminationInfo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $breederExaminationInfo->setPetType($petType)
                ->setBreeder($breeder);
            $formRequest = $request->request->get('breeder_examination_info');
            if ($formRequest['pedigree_organization'] == AnilineConf::PEDIGREE_ORGANIZATION_JKC) {
                $breederExaminationInfo->setPedigreeOrganization($formRequest['group_organization']);
            } else {
                $breederExaminationInfo->setPedigreeOrganization($formRequest['pedigree_organization']);
            }

            if ($formRequest['pedigree_organization'] != AnilineConf::PEDIGREE_ORGANIZATION_OTHER) {
                $breederExaminationInfo->setPedigreeOrganizationOther(null);
            }

            $breederExaminationInfo->setInputStatus(AnilineConf::ANILINE_INPUT_STATUS_INPUT_COMPLETE);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($breederExaminationInfo);
            $entityManager->flush();

            return $this->redirectToRoute('breeder_examination');
        }

        return $this->render('animalline/breeder/member/examination_info.twig', [
            'form' => $form->createView(),
            'isEdit' => $isEdit,
            'petType' => $petType == AnilineConf::ANILINE_PET_KIND_DOG ? '犬' : '猫'
        ]);
    }

    /**
     * 審査結果提出
     *
     * @Route("/breeder/member/examination/submit", name="breeder_examination_submit")
     */
    public function examination_submit(Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();

        // ブリーダーの審査ステータスを変更
        $breeder = $this->breedersRepository->find($this->getUser());
        $breeder->setExaminationStatus(AnilineConf::ANILINE_EXAMINATION_STATUS_NOT_CHECK);

        $entityManager->persist($breeder);

        // 犬舎・猫舎両方のパターンがあるため配列で取得
        $breederExaminationInfos = $this->breederExaminationInfoRepository->findBy([
            'Breeder' => $breeder,
        ]);

        // 審査情報のそれぞれの審査ステータスを変更
        foreach ($breederExaminationInfos as $breederExaminationInfo) {
            $breederExaminationInfo->setInputStatus(AnilineConf::ANILINE_INPUT_STATUS_SUBMIT);
            $entityManager->persist($breederExaminationInfo);
        }

        $entityManager->flush();

        return $this->redirectToRoute('breeder_examination');
    }

    /**
     * 検査状況確認
     *
     * @Route("/breeder/member/examination_status", name="breeder_examination_status")
     * @Template("animalline/breeder/member/examination_status.twig")
     */
    public function examination_status(Request $request, PaginatorInterface $paginator)
    {
        $dnaId = (int)$request->get('dna_id');
        if ($request->isMethod('POST') && $dnaId) {
            $dna = $this->dnaCheckStatusRepository->find($dnaId);
            if (!$dna) {
                throw new NotFoundHttpException();
            }

            $dna->setCheckStatus(AnilineConf::ANILINE_DNA_CHECK_STATUS_RESENT);
            $newDna = clone $dna;
            $newDna->setCheckStatus(AnilineConf::ANILINE_DNA_CHECK_STATUS_DEFAULT);

            $em = $this->getDoctrine()->getManager();
            $em->persist($dna);
            $em->persist($newDna);
            $em->flush();

            return $this->redirectToRoute('breeder_examination_status');
        }

        $userId = $this->getUser()->getId();
        $isAll = $request->get('is_all') ?? false;
        $results = $this->dnaQueryService->filterDnaBreederMember($userId, $isAll);
        $dnas = $paginator->paginate(
            $results,
            $request->query->getInt('page', 1),
            $request->query->getInt('item', AnilineConf::ANILINE_NUMBER_ITEM_PER_PAGE)
        );

        return compact('dnas');
    }
}
