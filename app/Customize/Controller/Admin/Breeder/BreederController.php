<?php

/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) EC-CUBE CO.,LTD. All Rights Reserved.
 *
 * http://www.ec-cube.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Customize\Controller\Admin\Breeder;

use Customize\Form\Type\Admin\BreedersType;
use Customize\Repository\BreederExaminationInfoRepository;
use Customize\Repository\BreedersRepository;
use Customize\Repository\BreederPetsRepository;
use Customize\Repository\BreedsRepository;
use Customize\Repository\DnaCheckKindsRepository;
use Customize\Repository\DnaCheckStatusRepository;
use Customize\Service\BreederQueryService;
use Customize\Service\DnaQueryService;
use Eccube\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Customize\Config\AnilineConf;
use Customize\Repository\BankAccountRepository;
use Customize\Repository\BreederPetImageRepository;
use Eccube\Repository\CustomerRepository;
use Customize\Repository\BreederEvaluationsRepository;
use Knp\Component\Pager\PaginatorInterface;
use Customize\Service\MailService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BreederController extends AbstractController
{
    /**
     * @var BreedersRepository
     */
    protected $breedersRepository;

    /**
     * @var BreedsRepository
     */
    protected $breedsRepository;

    /**
     * @var BreederPetImageRepository
     */
    protected $breederPetImageRepository;

    /**
     * @var BreederPetsRepository
     */
    protected $breederPetsRepository;

    /**
     * @var BreederQueryService
     */
    protected $breederQueryService;

    /**
     * @var BreederExaminationInfoRepository
     */
    protected $breederExaminationInfoRepository;

    /**
     * @var BankAccountRepository
     */
    protected $bankAccountRepository;

    /**
     * @var CustomerRepository
     */
    protected $customerRepository;

    /**
     * @var MailService
     */
    protected $mailService;

    /**
     * @var DnaCheckStatusRepository
     */
    protected $dnaCheckStatusRepository;

    /**
     * @var DnaQueryService
     */
    protected $dnaQueryService;

    /**
     * @var DnaCheckKindsRepository
     */
    protected $dnaCheckKindsRepository;

    /**
     * @var BreederEvaluationsRepository
     */
    protected $breederEvaluationsRepository;

    /**
     * breederController constructor.
     * @param BreedersRepository $breedersRepository
     * @param BreedsRepository $breedsRepository
     * @param BreederPetImageRepository $breederPetImageRepository
     * @param BreederQueryService $breederQueryService
     * @param BreederPetsRepository $breederPetsRepository
     * @param BreederExaminationInfoRepository $breederExaminationInfoRepository
     * @param BankAccountRepository $bankAccountRepository
     * @param CustomerRepository $customerRepository
     * @param MailService $mailService
     * @param DnaCheckStatusRepository $dnaCheckStatusRepository
     * @param DnaQueryService $dnaQueryService
     * @param DnaCheckKindsRepository $dnaCheckKindsRepository
     * @param BreederEvaluationsRepository $breederEvaluationsRepository
     */
    public function __construct(
        BreedersRepository               $breedersRepository,
        BreedsRepository                 $breedsRepository,
        BreederPetImageRepository        $breederPetImageRepository,
        BreederQueryService              $breederQueryService,
        BreederPetsRepository            $breederPetsRepository,
        BreederExaminationInfoRepository $breederExaminationInfoRepository,
        BankAccountRepository            $bankAccountRepository,
        CustomerRepository               $customerRepository,
        MailService                      $mailService,
        DnaCheckStatusRepository         $dnaCheckStatusRepository,
        DnaQueryService                  $dnaQueryService,
        DnaCheckKindsRepository          $dnaCheckKindsRepository,
        BreederEvaluationsRepository     $breederEvaluationsRepository
    ) {
        $this->breedersRepository = $breedersRepository;
        $this->breederPetsRepository = $breederPetsRepository;
        $this->breedsRepository = $breedsRepository;
        $this->breederQueryService = $breederQueryService;
        $this->breederPetImageRepository = $breederPetImageRepository;
        $this->breederExaminationInfoRepository = $breederExaminationInfoRepository;
        $this->bankAccountRepository = $bankAccountRepository;
        $this->customerRepository = $customerRepository;
        $this->mailService = $mailService;
        $this->dnaCheckStatusRepository = $dnaCheckStatusRepository;
        $this->dnaQueryService = $dnaQueryService;
        $this->dnaCheckKindsRepository = $dnaCheckKindsRepository;
        $this->breederEvaluationsRepository = $breederEvaluationsRepository;
    }

    /**
     * ブリーダー一覧
     *
     * @Route("/%eccube_admin_route%/breeder/breeder_list", name="admin_breeder_list")
     * @Template("@admin/Breeder/index.twig")
     */
    public function index(PaginatorInterface $paginator, Request $request)
    {
        $request = $request->query->all();
        $criteria = [];
        if (array_key_exists('breeder_name', $request)) {
            $criteria['breeder_name'] = $request['breeder_name'];
        }

        if (array_key_exists('examination_status', $request)) {
            switch ($request['examination_status']) {
                case 2:
                    $criteria['examination_status'] = [AnilineConf::ANILINE_EXAMINATION_STATUS_CHECK_OK, AnilineConf::ANILINE_EXAMINATION_STATUS_CHECK_NG];
                    break;
                case 3:
                    $criteria['examination_status'] = [AnilineConf::ANILINE_EXAMINATION_STATUS_NOT_CHECK];
                    break;
                default:
                    break;
            }
        }

        if (array_key_exists('create_date_from', $request)) {
            $criteria['create_date_from'] = $request['create_date_from'];
        }
        if (array_key_exists('create_date_to', $request)) {
            $criteria['create_date_to'] = $request['create_date_to'];
        }

        if (array_key_exists('update_date_from', $request)) {
            $criteria['update_date_from'] = $request['update_date_from'];
        }
        if (array_key_exists('update_date_to', $request)) {
            $criteria['update_date_to'] = $request['update_date_to'];
        }

        $order = [];
        $order['field'] = array_key_exists('field', $request) ? $request['field'] : 'create_date';
        $order['direction'] = array_key_exists('direction', $request) ? $request['direction'] : 'DESC';

        $results = $this->breedersRepository->filterBreederAdmin($criteria, $order);

        $breeders = $paginator->paginate(
            $results,
            array_key_exists('page', $request) ? $request['page'] : 1,
            AnilineConf::ANILINE_NUMBER_ITEM_PER_PAGE
        );
        $direction = 'ASC';
        if (array_key_exists('direction', $request)) {
            $direction = $request['direction'] == 'ASC' ? 'DESC' : 'ASC';
        }

        $breederstatus[0] = "申請待ち";
        $breederstatus[1] = "未審査";
        $breederstatus[2] = "審査済（許可）";
        $breederstatus[3] = "審査済（拒否）";

        return $this->render('@admin/Breeder/index.twig', [
            'breeders' => $breeders,
            'direction' => $direction,
            'breederstatus' => $breederstatus,
        ]);
    }

    /**
     * 登録内容編集ブリーダー管理
     *
     * @Route("/%eccube_admin_route%/breeder/edit/{id}", name="admin_breeder_edit", requirements={"id" = "\d+"})
     * @Template("@admin/Breeder/edit.twig")
     */
    public function Edit(Request $request, BreedersRepository $breedersRepository)
    {
        $breederData = $breedersRepository->find($request->get('id'));

        $builder = $this->formFactory->createBuilder(BreedersType::class, $breederData);

        $form = $builder->getForm();
        $form->handleRequest($request);

        $thumbnailPath = $request->get('thumbnail_path') ?: $breederData->getThumbnailPath();
        $licenseThumbnailPath = $request->get('license_thumbnail_path') ?: $breederData->getLicenseThumbnailPath();

        if ($form->isSubmitted() && $form->isValid()) {
            $breederData->setBreederPref($breederData->getPrefBreeder())
                ->setLicensePref($breederData->getPrefLicense())
                ->setThumbnailPath($thumbnailPath)
                ->setLicenseThumbnailPath($licenseThumbnailPath);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($breederData);
            $entityManager->flush();
            return $this->redirectToRoute('admin_breeder_list');
        }

        return [
            'thumbnailPath' => $thumbnailPath,
            'licenseThumbnailPath' => $licenseThumbnailPath,
            'breederData' => $breederData,
            'form' => $form->createView(),
            'id' => $request->get('id')
        ];
    }

    /**
     * 登録内容編集ブリーダー管理
     *
     * @Route("/%eccube_admin_route%/breeder/dna_check/list", name="admin_breeder_dna_check_list")
     * @Template("@admin/Breeder/dna_check_list.twig")
     */
    public function dnaCheckList(Request $request)
    {
        $arrayBreeder = [];
        $countPet = [];
        $count = [];
        $arrBreeder = [];
        $breederName = [];
        $arrPet = [];
        if ($request->get('order_date_year')) {
            $dnaCheckStatus = $this->dnaQueryService->findByDate($request->get('order_date_year'), $request->get('order_date_month'));
            foreach ($dnaCheckStatus as $item) {
                $arrBreeder[] = $item['breeder_id'];
                if ($item['pet_id']) {
                    $arrPet[] = $item['pet_id'];
                    $pet = $this->breederPetsRepository->find($item['pet_id']);
                    $count[$item['pet_id']] = count($this->dnaCheckKindsRepository->findBy(['Breeds' => $pet->getBreedsType()]));
                }
            }
            $arrayPet = array_count_values($arrPet);
            $arrayBreeder = array_count_values($arrBreeder);
            foreach ($arrayBreeder as $key => $amount) {
                $countPet[$key] = 0;
                $breeder = $this->breedersRepository->find($key);
                $breederName[$key] = $breeder->getBreederName();
                foreach ($arrayPet as $petId => $value) {
                    $pet = $this->breederPetsRepository->find($petId);
                    $breederId = $pet->getBreeder()->getId();
                    if ($breederId == $key) {
                        $countPet[$key] = $countPet[$key] + $count[$petId] * $value;
                    }
                }
            }
        }

        return [
            'breederName' => $breederName,
            'arrayBreeder' => $arrayBreeder,
            'countPet' => $countPet
        ];
    }

    /**
     * ブリーダー評価一覧
     *
     * @Route("/%eccube_admin_route%/breeder/evaluation", name="admin_breeder_evaluation")
     * @Template("@admin/Breeder/evaluation.twig")
     */
    public function evaluation(PaginatorInterface $paginator, Request $request)
    {
        $evaluations = $paginator->paginate(
            $this->breederEvaluationsRepository->findBy([], ['update_date' => 'DESC']),
            $request->query->getInt('page', 1),
            AnilineConf::ANILINE_NUMBER_ITEM_PER_PAGE
        );

        if ($request->isMethod('POST')) {
            $result = (int)$request->get('is_active');
            $id = (int)$request->get('id');

            $evaluation = $this->breederEvaluationsRepository->find($id);
            $evaluation->setIsActive($result);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($evaluation);
            $entityManager->flush();

            $breeder = $evaluation->getPet()->getBreeder();
            $avgEvaluation = $this->breederQueryService->calculateBreederRank($breeder->getId());
            $breeder->setBreederRank($avgEvaluation);
            $entityManager->persist($breeder);

            $entityManager->flush();

            $this->addSuccess('公開ステータスを変更しました。', 'admin');
            return $this->redirectToRoute('admin_breeder_evaluation');
        }

        return [
            'evaluations' => $evaluations,
        ];
    }

    /**
     * 銀行口座
     *
     * @Route("/%eccube_admin_route%/breeder/bank_account/{id}", name="admin_breeder_bank_account", requirements={"id" = "\d+"})
     * @Template("@admin/Breeder/bank_account.twig")
     */
    public function bankAccount(Request $request): array
    {
        if (!$BankAccount = $this->bankAccountRepository->find($request->get('id'))) {
            throw new NotFoundHttpException();
        }

        return [
            'BankAccount' => $BankAccount
        ];
    }
}
