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

use Customize\Repository\DnaCheckKindsRepository;
use Customize\Repository\DnaCheckStatusHeaderRepository;
use Customize\Config\AnilineConf;
use Customize\Entity\DnaCheckStatusDetail;
use Customize\Repository\BreederPetsRepository;
use Customize\Repository\ConservationPetsRepository;
use Customize\Repository\DnaCheckStatusRepository;
use Eccube\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Exception;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Customize\Service\VeqtaQueryService;
use Knp\Component\Pager\PaginatorInterface;
use TCPDF;

class VeqtaController extends AbstractController
{
    /**
     * @var DnaCheckStatusHeaderRepository
     */
    protected $dnaCheckStatusHeaderRepository;

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
     * @var VeqtaQueryService
     */
    protected $veqtaQueryService;

    /**
     * @var DnaCheckKindsRepository
     */
    protected $dnaCheckKindsRepository;

    /**
     * VeqtaController constructor.
     *
     * @param DnaCheckStatusHeaderRepository $dnaCheckStatusHeaderRepository
     * @param BreederPetsRepository $breederPetsRepository
     * @param ConservationPetsRepository $conservationPetsRepository
     * @param DnaCheckStatusRepository $dnaCheckStatusRepository
     * @param VeqtaQueryService $veqtaQueryService
     * @param DnaCheckKindsRepository $dnaCheckKindsRepository
     */

    public function __construct(
        DnaCheckStatusHeaderRepository $dnaCheckStatusHeaderRepository,
        BreederPetsRepository          $breederPetsRepository,
        ConservationPetsRepository     $conservationPetsRepository,
        DnaCheckStatusRepository       $dnaCheckStatusRepository,
        VeqtaQueryService              $veqtaQueryService,
        DnaCheckKindsRepository        $dnaCheckKindsRepository
    ) {
        $this->dnaCheckStatusHeaderRepository = $dnaCheckStatusHeaderRepository;
        $this->breederPetsRepository = $breederPetsRepository;
        $this->conservationPetsRepository = $conservationPetsRepository;
        $this->dnaCheckStatusRepository = $dnaCheckStatusRepository;
        $this->veqtaQueryService = $veqtaQueryService;
        $this->dnaCheckKindsRepository = $dnaCheckKindsRepository;
    }

    /**
     * Register and receive DNA kit result
     *
     * @Route("/veqta/", name="veqta_index")
     * @Template("animalline/veqta/index.twig")
     */
    public function index(): array
    {
        return [];
    }

    /**
     * Pet list
     *
     * @Route("/veqta/pet_list", name="veqta_pet_list")
     * @Template("animalline/veqta/pet_list.twig")
     */
    public function pet_list(Request $request, PaginatorInterface $paginator): array
    {
        $dnasResult = $this->veqtaQueryService->filterPetList();
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
     * @Route("/veqta/arrive", name="veqta_arrive")
     * @Template("animalline/veqta/arrive.twig")
     */
    public function arrive(Request $request)
    {
        if ($request->get('dna-id') && $request->isMethod('POST')) {
            $dna = $this->dnaCheckStatusRepository->find((int)$request->get('dna-id'));
            $dna->setCheckStatus(AnilineConf::ANILINE_DNA_CHECK_STATUS_CHECKING);
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            return $this->redirectToRoute('veqta_arrive');
        }
        return [];
    }

    /**
     * Register and receive DNA kit result
     *
     * @Route("/veqta/arrive/get_user", name="arrive_get_user")
     * @param Request $request
     * @return JsonResponse
     */
    public function getArriveUser(Request $request): JsonResponse
    {
        $shippingName = null;
        $show = false;
        $dnaCheckStatus = $this->dnaCheckStatusRepository->find($request->get('id'));
        if ($dnaCheckStatus) {
            $header = $this->dnaCheckStatusHeaderRepository->find($dnaCheckStatus->getDnaHeader());
            if ($dnaCheckStatus->getCheckStatus() == 3) {
                $show = true;
                $shippingName = $header->getShippingName();
            }
        }
        $data = [
            'shipping_name' => $shippingName,
            'isDisable' => $show,
            'dnaId' => $request->get('id')
        ];
        return new JsonResponse($data);
    }

    /**
     * Dna result regist.
     * ※差し替え予定
     * @Route("/veqta/result", name="veqta_result")
     * @Template("animalline/veqta/result.twig")
     * @throws Exception
     */
    public function result(Request $request)
    {
        if (!$request->isMethod('POST')) {
            return;
        }

        $barcode = $request->get('barcode');
        $checkStatus = $request->get('check_status');
        $siteType = $barcode[0];
        $dnaId = substr($barcode, 1);

        $Dna = $this->dnaCheckStatusRepository->findOneBy(['id' => $dnaId, 'site_type' => $siteType]);
        if (!$Dna) {
            throw new NotFoundHttpException();
        }
        $Pet = $siteType == AnilineConf::ANILINE_SITE_TYPE_BREEDER ?
            $this->breederPetsRepository->find($Dna->getPetId()) :
            $this->conservationPetsRepository->find($Dna->getPetId());
        if (!$Pet) {
            throw new NotFoundHttpException();
        }

        switch ($checkStatus) {
            case AnilineConf::ANILINE_DNA_CHECK_STATUS_SPECIMEN_ABNORMALITY:
                $Dna->setCheckStatus($checkStatus);
                break;
            case AnilineConf::ANILINE_DNA_CHECK_STATUS_TEST_NG:
                $Dna->setCheckStatus($checkStatus);
                $Pet->setDnaCheckResult(AnilineConf::DNA_CHECK_RESULT_3);
                break;
            default:
                $Dna->setCheckStatus(AnilineConf::ANILINE_DNA_CHECK_STATUS_PASSED);
                $Pet->setDnaCheckResult($checkStatus == 61 ? AnilineConf::DNA_CHECK_RESULT_1 : AnilineConf::DNA_CHECK_RESULT_2); // 61: クリア, 62: キャリア.
        }

        $savePath = $this->copyFile($request->get('file_name'));
        $Dna->setFilePath($savePath);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($Dna);
        $entityManager->persist($Pet);
        $entityManager->flush();

        return $this->redirectToRoute('veqta_result');
    }

    /**
     * DNA検査結果登録.
     * @Route("/veqta/result_regist", name="veqta_result_regist")
     * @Template("animalline/veqta/result_regist.twig")
     */
    public function result_regist(Request $request)
    {
        if (!$request->isMethod('POST')) {
            return;
        }

        $barcode = $request->get('barcode');
        $checkStatus = $request->get('check_status_total');
        $siteType = $barcode[0];
        $dnaId = substr($barcode, 1);

        $Dna = $this->dnaCheckStatusRepository->findOneBy(['id' => $dnaId, 'site_type' => $siteType]);
        if (!$Dna) {
            throw new NotFoundHttpException();
        }
        $Pet = $siteType == AnilineConf::ANILINE_SITE_TYPE_BREEDER ?
            $this->breederPetsRepository->find($Dna->getPetId()) :
            $this->conservationPetsRepository->find($Dna->getPetId());
        if (!$Pet) {
            throw new NotFoundHttpException();
        }

        switch ($checkStatus) {
            case AnilineConf::ANILINE_DNA_CHECK_STATUS_SPECIMEN_ABNORMALITY:
                $Dna->setCheckStatus($checkStatus);
                break;
            case AnilineConf::ANILINE_DNA_CHECK_STATUS_TEST_NG:
                $Dna->setCheckStatus($checkStatus);
                $Pet->setDnaCheckResult(AnilineConf::DNA_CHECK_RESULT_3);
                break;
            default:
                $Dna->setCheckStatus(AnilineConf::ANILINE_DNA_CHECK_STATUS_PASSED);
                $Pet->setDnaCheckResult($checkStatus == 61 ? AnilineConf::DNA_CHECK_RESULT_1 : AnilineConf::DNA_CHECK_RESULT_2); // 61: クリア, 62: キャリア.
        }

        $entityManager = $this->getDoctrine()->getManager();

        if ($checkStatus != AnilineConf::ANILINE_DNA_CHECK_STATUS_SPECIMEN_ABNORMALITY &&
            $dnaDetailData = $request->get('check_status')) {
            for ($i = 0; $i < count($dnaDetailData['kind']); $i++) {
                $DnaDetail = (new DnaCheckStatusDetail)
                    ->setCheckResult($dnaDetailData['status'][$dnaDetailData['kind'][$i]])
                    ->setCheckStatus($Dna)
                    ->setCheckKinds($this->dnaCheckKindsRepository->find($dnaDetailData['kind'][$i]));
                $entityManager->persist($DnaDetail);
            }
        }

        $entityManager->persist($Dna);
        $entityManager->persist($Pet);
        $entityManager->flush();

        return $this->redirectToRoute('veqta_result_regist');
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
     * @Route("/read_barcode", name="read_barcode", methods={"GET"})
     * @param Request $request
     * @return JsonResponse
     */
    public function readBarCode(Request $request): JsonResponse
    {
        $barcode = $request->get('barcode');
        $siteType = $barcode[0];
        $dnaId = substr($barcode, 1);

        $Dna = $this->dnaCheckStatusRepository->findOneBy(['id' => $dnaId, 'site_type' => $siteType]);
        if ($Dna) {
            if ($Dna->getSiteType() == AnilineConf::SITE_CATEGORY_BREEDER) {
                $pet = $this->breederPetsRepository->find($Dna->getPetId());
            } else {
                $pet = $this->conservationPetsRepository->find($Dna->getPetId());
            }
            $data['breed'] = $pet->getBreedsType()->getBreedsName();
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
        } else {
            $data['hasRecord'] = false;
        }
        return new JsonResponse($data);
    }

    /**
     * Read barcode.
     *
     * @Route("/exportPDF", name="export_pdf", methods={"GET"})
     */
    public function indexAction(){
        $html = $this->renderView(
            'pdf/dna_check.html.twig',
            [
                'title' => 'Something',
                'name_button' => 'abc',
            ]
        );

        $this->returnPDFResponseFromHTML($html);
    }

    public function returnPDFResponseFromHTML($html){
        $pdf = new TCPDF('vertical', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetTitle(('Our Code World Title'));
        $pdf->setFontSubsetting(true);
        $pdf->SetFont('helvetica', '', 11, '', true);
        //$pdf->SetMargins(20,20,40, true);
        $pdf->AddPage();
        
        $filename = 'ourcodeworld_pdf_demo';
        
        $pdf->writeHTMLCell($w = 0, $h = 0, $x = '', $y = '', $html, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = '', $autopadding = true);
        $pdf->Output($filename.".pdf",'D'); // This will output the PDF as a response directly
    }
}
