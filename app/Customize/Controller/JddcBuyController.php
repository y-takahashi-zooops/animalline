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

namespace Customize\Controller;

use Carbon\Carbon;
use Customize\Repository\DnaCheckKindsRepository;
use Customize\Repository\DnaCheckStatusHeaderRepository;
use Customize\Config\AnilineConf;
use Customize\Entity\DnaCheckStatusDetail;
use Customize\Repository\BreederPetsRepository;
use Customize\Repository\ConservationPetsRepository;
use Customize\Repository\DnaCheckStatusRepository;
use Customize\Repository\DnaCheckStatusDetailRepository;
use Eccube\Repository\CustomerRepository;
use Customize\Service\VeqtaPdfService;
use Eccube\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Exception;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Customize\Service\JddcQueryService;
use Knp\Component\Pager\PaginatorInterface;
use Customize\Service\MailService;
use Customize\Repository\DnaSalesHeaderRepository;
use Customize\Repository\DnaSalesStatusRepository;
use Customize\Repository\DnaSalesDetailRepository;
use Doctrine\ORM\EntityManagerInterface;

class JddcBuyController extends AbstractController
{
    /**
     * @var DnaCheckStatusHeaderRepository
     */
    protected $dnaCheckStatusHeaderRepository;

    /**
     * @var MailService
     */
    protected $mailService;

    /**
     * @var BreederPetsRepository
     */
    protected $breederPetsRepository;

    /**
     * @var ConservationPetsRepository
     */
    protected $conservationPetsRepository;

    /**
     * @var DnaCheckStatusRepository
     */
    protected $dnaCheckStatusRepository;

    /**
     * @var JddcQueryService
     */
    protected $jddcQueryService;

    /**
     * @var DnaCheckKindsRepository
     */
    protected $dnaCheckKindsRepository;

    /**
     * @var DnaCheckStatusDetailRepository
     */
    protected $dnaCheckStatusDetailRepository;

    /**
     * @var CustomerRepository
     */
    protected $customerRepository;

    /**
     * @var DnaSalesHeaderRepository
     */
    protected $dnaSalesHeaderRepository;

    /**
     * @var DnaSalesStatusRepository
     */
    protected $dnaSalesStatusRepository;

    /**
     * @var DnaSalesDetailRepository
     */
    protected $dnaSalesDetailRepository;
    

    /**
     * VeqtaController constructor.
     *
     * @param DnaCheckStatusHeaderRepository $dnaCheckStatusHeaderRepository
     * @param BreederPetsRepository $breederPetsRepository
     * @param ConservationPetsRepository $conservationPetsRepository
     * @param DnaCheckStatusRepository $dnaCheckStatusRepository
     * @param JddcQueryService $jddcQueryService
     * @param DnaCheckKindsRepository $dnaCheckKindsRepository
     * @param MailService $mailService
     * @param DnaCheckStatusDetailRepository $dnaCheckStatusDetailRepository
     * @param CustomerRepository $customerRepository
     * @param DnaSalesHeaderRepository $dnaSalesHeaderRepository
     * @param DnaSalesStatusRepository $dnaSalesStatusRepository
     * @param DnaSalesDetailRepository $dnaSalesDetailRepository
     */

    public function __construct(
        DnaCheckStatusHeaderRepository $dnaCheckStatusHeaderRepository,
        BreederPetsRepository          $breederPetsRepository,
        ConservationPetsRepository     $conservationPetsRepository,
        DnaCheckStatusRepository       $dnaCheckStatusRepository,
        JddcQueryService               $jddcQueryService,
        DnaCheckKindsRepository        $dnaCheckKindsRepository,
        MailService $mailService,
        DnaCheckStatusDetailRepository $dnaCheckStatusDetailRepository,
        CustomerRepository $customerRepository,
        DnaSalesHeaderRepository $dnaSalesHeaderRepository,
        DnaSalesStatusRepository $dnaSalesStatusRepository,
        DnaSalesDetailRepository $dnaSalesDetailRepository,
        EntityManagerInterface $entityManager,
    ) {
        $this->dnaCheckStatusHeaderRepository = $dnaCheckStatusHeaderRepository;
        $this->breederPetsRepository = $breederPetsRepository;
        $this->conservationPetsRepository = $conservationPetsRepository;
        $this->dnaCheckStatusRepository = $dnaCheckStatusRepository;
        $this->jddcQueryService = $jddcQueryService;
        $this->dnaCheckKindsRepository = $dnaCheckKindsRepository;
        $this->mailService = $mailService;
        $this->dnaCheckStatusDetailRepository = $dnaCheckStatusDetailRepository;
        $this->customerRepository = $customerRepository;
        $this->dnaSalesHeaderRepository = $dnaSalesHeaderRepository;
        $this->dnaSalesStatusRepository = $dnaSalesStatusRepository;
        $this->dnaSalesDetailRepository = $dnaSalesDetailRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * Pet list
     *
     * @Route("/jddc/complete_list_buy", name="jddc_complete_list_buy")
     * @Template("animalline/jddc/complete_list_buy.twig")
     */
    public function complete_list_buy(Request $request, PaginatorInterface $paginator): array
    {
        $from = $request->get("create_date_from");
        $to = $request->get("create_date_to");

        $builder = $this->dnaSalesStatusRepository->createQueryBuilder('dh')
            ->andWhere('dh.check_status = 3');

        if($from){
            $builder->andWhere('dh.create_date >= :create_date_from')
                ->setParameter('create_date_from', $from);
        }
        if($to){
            $builder->andWhere('dh.create_date <= :create_date_to')
                ->setParameter('create_date_to', $to);
        }
        
        $dnasResult = $builder->getQuery()->getResult();

        $dnas = $paginator->paginate(
            $dnasResult,
            $request->query->getInt('page', 1),
            $request->query->getInt('item', AnilineConf::ANILINE_NUMBER_ITEM_PER_PAGE)
        );

        // get check kinds
        foreach ($dnas as $idx => $dna) {
            $kinds = $this->dnaCheckKindsRepository->findBy(['Breeds' => $dna['breeds_id'], "delete_flg" => 0]);
            $dna['check_kinds'] = array_map(function ($item) {
                return $item->getCheckKind();
            }, $kinds);
            $dnas[$idx] = $dna;
        }

        return compact(
            'dnas'
        );
    }

    /**
     * Pet list
     *
     * @Route("/jddc/pet_list_buy", name="jddc_pet_list_buy")
     * @Template("animalline/jddc/pet_list_buy.twig")
     */
    public function pet_list_buy(Request $request, PaginatorInterface $paginator): array
    {
        $builder = $this->dnaSalesStatusRepository->createQueryBuilder('dh')
            ->andWhere('dh.check_status = 1');

        $dnasResult = $builder->getQuery()->getResult();

        $dnas = $paginator->paginate(
            $dnasResult,
            $request->query->getInt('page', 1),
            $request->query->getInt('item', AnilineConf::ANILINE_NUMBER_ITEM_PER_PAGE)
        );

        return compact(
            'dnas'
        );
    }

    /**
     * Receive DNA kit result
     *
     * @Route("/jddc/arrive_buy", name="jddc_arrive_buy")
     * @Template("animalline/jddc/arrive_buy.twig")
     */
    public function arrive_buy(Request $request)
    {
        if ($request->get('dna-id') && $request->isMethod('POST')) {
            $dna = $this->dnaSalesStatusRepository->find((int)$request->get('dna-id'));
            $dna->setCheckStatus(2);
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            return $this->redirectToRoute('jddc_arrive_buy');
        }
        return [];
    }

    /**
     * Register and receive DNA kit result
     *
     * @Route("/jddc/arrive/get_user_buy", name="jddc_arrive_get_user_buy")
     * @param Request $request
     * @return JsonResponse
     */
    public function getArriveUserBuy(Request $request): JsonResponse
    {
        $barcode = $request->get('barcode');
        $shippingName = null;
        $show = false;
        $petBirthday = null;
        $petKind = null;
        $petType = null;
        $dnaId = substr($barcode, 1);
        $dnaCheckStatus = $this->dnaSalesStatusRepository->find($dnaId);
        if ($dnaCheckStatus) {
            $header = $this->dnaSalesHeaderRepository->find($dnaCheckStatus->getDnaSalesHeader());
            if ($dnaCheckStatus->getCheckStatus() == 1) {
                $show = true;
                $shippingName = $header->getShippingName();
                $petBirthday = $dnaCheckStatus->getBirthday() ? $dnaCheckStatus->getBirthday()->format('Y/m/d') : null;
                $petKind = $dnaCheckStatus->getPetKind() == AnilineConf::ANILINE_PET_KIND_DOG ? '犬' : '猫';
                $petType = $dnaCheckStatus->getBreedsType()->getBreedsName();
            }
        }
        $data = [
            'shipping_name' => $shippingName,
            'isDisable' => $show,
            'dnaId' => intval($dnaId),
            'petBirthday' => $petBirthday,
            'petKind' => $petKind,
            'petType' => $petType
        ];
        return new JsonResponse($data);
    }

    /**
     * DNA検査結果登録.
     * @Route("/jddc/result_regist_buy", name="jddc_result_regist_buy")
     * @Template("animalline/jddc/result_regist_buy.twig")
     * @throws Exception
     */
    public function result_regist_buy(Request $request, VeqtaPdfService $veqtaPdfService)
    {
        if (!$request->isMethod('POST')) {
            return [];
        }

        $barcode = $request->get('barcode');
        $checkStatus = $request->get('check_status_total');
        $dnaId = substr($barcode, 1);

        $Dna = $this->dnaSalesStatusRepository->findOneBy(['id' => $dnaId]);
        if (!$Dna) {
            throw new NotFoundHttpException();
        }
        
        $countCheckKind = count($this->dnaCheckKindsRepository->findBy(['Breeds' => $Pet->getBreedsType(), "delete_flg" => 0]));

        //更新の時は前の登録データ削除（フラグを立ててメールを送らない）
        $entityManager = $this->entityManager;
        $lists = $this->dnaCheckStatusDetailRepository->findBy(['CheckStatus' => $Dna]);
        $is_sendmail = true;
        foreach($lists as $list){
            $entityManager->remove($list);
            $is_sendmail = false;
        }

        //メール送信準備
        $dna_header = $Dna->getDnaHeader();
        $customer_id = $dna_header->getRegisterId();
        $Customer = $this->customerRepository->find($customer_id);

        switch ($checkStatus) {
            case AnilineConf::ANILINE_DNA_CHECK_STATUS_SPECIMEN_ABNORMALITY:
                $Dna->setCheckStatus($checkStatus);

                if($is_sendmail){
                    $this->mailService->sendDnaCheckRetry($Customer,$Dna);
                }
                break;
            case AnilineConf::ANILINE_DNA_CHECK_STATUS_TEST_NG:
            case 63:
                $Dna->setCheckStatus(AnilineConf::ANILINE_DNA_CHECK_STATUS_TEST_NG);
                $Pet->setDnaCheckResult(AnilineConf::DNA_CHECK_RESULT_CHECK_NG);
                $Pet->setIsActive(2);
                
                if($checkStatus == 63){
                    $restext = "キャリア（優性）";
                }
                else{
                    $restext = "アフェクテッド";
                }

                //ＮＧの場合メールを送る
                // if($is_sendmail){
                //     $this->mailService->sendDnaCheckNg($Customer,$Dna,$restext);
                // }
                break;
            default: // 61: クリア, 62: キャリア.
                $Dna->setCheckStatus(AnilineConf::ANILINE_DNA_CHECK_STATUS_PASSED);
                $Pet->setDnaCheckResult(AnilineConf::DNA_CHECK_RESULT_CHECK_OK);

                if($checkStatus == 61){
                    $restext = "クリア";
                }
                else{
                    $restext = "キャリア（劣性）";
                }

                // if($is_sendmail){
                //     $this->mailService->sendDnaCheckOk($Customer,$Dna,$restext);
                // }
        }

        if (
            $checkStatus != AnilineConf::ANILINE_DNA_CHECK_STATUS_SPECIMEN_ABNORMALITY &&
            $dnaDetailData = $request->get('check_status')
        ) {
            for ($i = 0; $i < count($dnaDetailData['kind']); $i++) {
                $DnaDetail = (new DnaCheckStatusDetail)
                    ->setCheckResult($dnaDetailData['status'][$dnaDetailData['kind'][$i]])
                    ->setCheckStatus($Dna)
                    ->setCheckKinds($this->dnaCheckKindsRepository->find($dnaDetailData['kind'][$i]));
                $entityManager->persist($DnaDetail);
            }
            $entityManager->flush();
        }
        $entityManager->persist($Pet);

        if ($siteType == AnilineConf::ANILINE_SITE_TYPE_BREEDER) {
            $arrData = [];
            $arrData['breeder_name'] = $request->get('barcode');
            $arrData['pet'] = $Pet;
            $arrData['result'] = $Dna->getCheckStatus();
            $checkDetails = [];
            foreach ($Dna->getCheckStatusDetails() as $item) {
                $itemArr = [];
                $itemArr['check_kind_name'] = $item->getCheckKinds() ? $item->getCheckKinds()->getCheckKind() : '';
                $itemArr['check_kind_result'] = $item->getCheckResult();
                $checkDetails[] = $itemArr;
            }
            $arrData['check_kinds'] = $checkDetails;
            
            $veqtaPdfService->makePdf($arrData);

            $pdfDnaDir = 'var/pdf/dna';
            if (!file_exists($pdfDnaDir) && !mkdir($pdfDnaDir, 0777, true)) {
                throw new Exception('Failed to create folder.');
            }
            $pdfPath = $pdfDnaDir . '/JddcDNAReport_' . $Dna->getId() . '.pdf';
            try {
                $veqtaPdfService->Output($_SERVER['DOCUMENT_ROOT'] . $pdfPath, 'F');
            } catch (Exception $e) {
                throw new Exception($e->getMessage(), 500);
            }

            $Dna->setFilePath($pdfPath);
            $Dna->setCheckReturnDate(Carbon::now());
            $Dna->setDnaCheckCount($countCheckKind);
        }
        
        $entityManager->persist($Dna);
        $entityManager->flush();

        return $this->redirectToRoute('jddc_result_regist');
    }

    /**
     * Copy file from old path to new path.
     * @param string $fileName
     * @return string $toPath
     * @throws Exception
     */
    private function copyFile(string $fileName): string
    {
        if (!$fileName) {
            return '';
        }

        $toFolder = AnilineConf::ANILINE_IMAGE_URL_BASE . '/license/';
        if (!file_exists($toFolder) && !mkdir($toFolder, 0777, true)) {
            throw new Exception('Failed to create folder.');
        }
        $fromPath = 'var/tmp/' . $fileName;
        $toPath = $toFolder . $fileName;
        copy($fromPath, $toPath); // ? should be moved instead of copy.

        return $toPath;
    }

    /**
     * Upload file.
     * @Route("/upload_file", name="upload_file", methods={"POST"})
     */
    public function uploadFile(Request $request): JsonResponse
    {
        $file = $request->files->get('file');
        $folder = 'var/tmp/';

        try {
            $file->move($folder, $file->getClientOriginalName());
        } catch (Exception $e) {
            return new JsonResponse($e->getMessage(), 500);
        }

        return new JsonResponse(['file_name' => $file->getClientOriginalName()]);
    }

    /**
     * Read barcode.
     *
     * @Route("/jddc_read_barcode_buy", name="jddc_read_barcode_buy", methods={"GET"})
     * @param Request $request
     * @return JsonResponse
     */
    public function readBarCodeBuy(Request $request): JsonResponse
    {
        $barcode = $request->get('barcode');
        $dnaId = substr($barcode, 1);

        $data['hasRecord'] = false;
        $Dna = $this->dnaSalesStatusRepository->findOneBy(['id' => $dnaId]);
        if ($Dna) {
            $data['breed'] = $Dna->getBreedsType() ? $Dna->getBreedsType()->getBreedsName() : '';
            $data['pet_birthday'] = $Dna->BirthDay() ? $Dna->BirthDay()->format('Y/m/d') : '';
            if (!$Dna->getPetKind()) {
                $data['pet_kind'] = '';
            } else {
                $data['pet_kind'] = $pet->getPetKind() == AnilineConf::ANILINE_PET_KIND_DOG ? '犬' : '猫';
            }

            $checkKinds = [];
            foreach ($this->dnaCheckKindsRepository->findBy(
                ['Breeds' => $pet->getBreedsType(), 'delete_flg' => 0],
                ['update_date' => 'DESC', 'id' => 'DESC']
            ) as $item) {
                $itemArr = [];
                $itemArr['id'] = $item->getId();
                $itemArr['check_kind'] = $item->getCheckKind();
                $checkKinds[] = $itemArr;
            }
            $data['checkKind'] = $checkKinds;
            $data['shippingName'] = $Dna->getDnaHeader()->getShippingName();
            $data['hasRecord'] = $Dna->getCheckStatus() === AnilineConf::ANILINE_DNA_CHECK_STATUS_CHECKING;
            $data['hasRecord'] = true;
            
        }
        return new JsonResponse($data);
    }
}
