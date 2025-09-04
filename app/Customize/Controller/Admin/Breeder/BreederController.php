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
use Symfony\Component\HttpFoundation\StreamedResponse;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;

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
        $breederstatus[4] = "取消済";

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
            $entityManager = $this->entityManager;

            if ($request->get('breeders')['is_active'] == AnilineConf::IS_ACTIVE_PRIVATE) {
                $breederPets = $this->breederPetsRepository->findBy(['Breeder' => $breederData]);
                foreach ($breederPets as $breederPet) {
                    $breederPet->setIsActive(AnilineConf::IS_ACTIVE_PRIVATE);
                    $entityManager->persist($breederPet);
                }
            }
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
        $dnaCheckCount = [];
        $arrBreeder = [];
        $breederName = [];
        $arrCount = [];

        $dnaCheckStatus = null;
        //集計結果からペット情報参照
        $arrResult = [];
        $pet_kind = 1;
        $BreedsType = 1;
        
        if ($request->get('order_date_year')) {
            $dnaCheckStatus = $this->dnaQueryService->findByDate(
                $request->get('order_date_year'), $request->get('order_date_month'), $request->get('order_date_day'),
                $request->get('order_date_year2'), $request->get('order_date_month2'), $request->get('order_date_day2'),
                $request->get('dna_check_org')
            );

            $pet_kind = $request->get('pet_kind');
            $BreedsType = $request->get('BreedsType');

            $fp = fopen("var/tmp/dna_check_list.csv","w");

            foreach ($dnaCheckStatus as $item) {
                $pet = $this->breederPetsRepository->find($item["pet_id"]);
                $breeds = $pet->getBreedsType();

                if(!$BreedsType || $breeds->getId() == $BreedsType){
                    $arritem = [
                        "result_date" => $item["result_date"]->format("Y/m/d"),
                        "name" => $item["name"],
                        "barcode" => $item["stype"].sprintf("%05d",$item["dnaid"]),
                        "breeds_name" => $breeds->getBreedsName(),
                        "count" => $item["count"],
                    ];
                    $arrResult[] = $arritem;

                    $arrcsv = [
                        "result_date" => $item["result_date"]->format("Y/m/d"),
                        "name" => mb_convert_encoding($item["name"],"SJIS"),
                        "barcode" => $item["stype"].sprintf("%05d",$item["dnaid"]),
                        "breeds_name" => mb_convert_encoding($breeds->getBreedsName(),"SJIS"),
                        "count" => $item["count"],
                    ];

                    fputcsv($fp,$arrcsv);
                }
            }
            fclose($fp);
            /*
            $arrayBreeder = array_count_values($arrBreeder);
            foreach ($arrayBreeder as $key => $amount) {
                $dnaCheckCount[$key] = 0;
                $breeder = $this->breedersRepository->find($key);
                $breederName[$key] = $breeder->getBreederName();
            }
            */
        }
        
        return [
            'dnaCheckStatus' => $arrResult,
            'pet_kind' => $pet_kind,
            'BreedsType' => $BreedsType,
        ];
    }

    /**
     * 登録内容編集ブリーダー管理
     * @Route("/%eccube_admin_route%/breeder/dna_check/csv_get", name="admin_breeder_dna_check_csv_get")
     */
    public function dnaCheckCsvGet(Request $request)
    {
        $filename = 'dna_check_list_'.(new \DateTime())->format('YmdHis').'.csv';
        
        $response = new StreamedResponse();

        $response->setCallback(function () use ($request) {
            $filePath = 'var/tmp/dna_check_list.csv';

            $fp = fopen('php://output', 'w');
            $fp2 = fopen($filePath , 'r');

            
            $headers = mb_convert_encoding("検査完了日,ブリーダー名,検査ID,犬種／猫種,	検査項目数\r\n","SJIS");
            fputs($fp,$headers);
            
            while(!feof($fp2)){
                $row = fgets($fp2);
                fputs($fp,$row);
            }
            
            fclose($fp);
            fclose($fp2);
        });

        $response->headers->set('Content-Type', 'application/octet-stream');
        $response->headers->set('Content-Disposition', 'attachment; filename=' . $filename);

        $response->send();

        return $response;
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
            $entityManager = $this->entityManager;
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
     * 銀行口座情報
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

    /**
     * CSVダウンロード
     *
     * @Route("/%eccube_admin_route%/breeder/get_csvlist", name="admin_breeder_get_csvlist")
     * 
     */
    public function getCsvList(Request $request)
    {
        $filename = 'breeders_'.(new \DateTime())->format('YmdHis').'.csv';
        $filePath = 'var/breeders.csv';
        
        $response = new StreamedResponse();

        $response->setCallback(function () use ($request) {
            $fp = fopen('php://output', 'w');

            $headers = mb_convert_encoding("id,ブリーダー名,犬舎/猫舎名,電話番号,メールアドレス,郵便番号,住所\r\n","SJIS");
            fputs($fp,$headers);

            $breeders = $this->breedersRepository->findAll();
            foreach($breeders as $breeder){
                $row = array();
                $customer = $this->customerRepository->find($breeder->getId());

                $row[] = $breeder->getId();
                $row[] =  mb_convert_encoding($breeder->getBreederName(),"SJIS");
                $row[] =  mb_convert_encoding($breeder->getLicenseHouseName(),"SJIS");
                $row[] =  mb_convert_encoding("'".$breeder->getBreederTel(),"SJIS");
                $row[] =  mb_convert_encoding($customer->getEmail(),"SJIS");
                $row[] =  mb_convert_encoding($breeder->getBreederZip(),"SJIS");
                $row[] =  mb_convert_encoding($breeder->getBreederPref().$breeder->getBreederCity().$breeder->getBreederAddress(),"SJIS");

                fputcsv($fp,$row);
            }

            fclose($fp);
        });

        $response->headers->set('Content-Type', 'application/octet-stream');
        $response->headers->set('Content-Disposition', 'attachment; filename=' . $filename);

        $response->send();

        return $response;
    }
}
