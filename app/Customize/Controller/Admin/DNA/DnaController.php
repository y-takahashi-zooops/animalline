<?php

namespace Customize\Controller\Admin\DNA;

use Carbon\Carbon;
use Customize\Config\AnilineConf;
use Customize\Entity\DnaCheckKinds;
use Customize\Entity\DnaCheckKindsEc;
use Customize\Repository\BreederPetsRepository;
use Customize\Repository\ConservationPetsRepository;
use Customize\Entity\DnaCheckStatus;
use Customize\Repository\BreedsRepository;
use Customize\Repository\DnaCheckKindsEcRepository;
use Customize\Repository\DnaCheckKindsRepository;
use Customize\Repository\DnaCheckStatusHeaderRepository;
use Customize\Repository\DnaCheckStatusRepository;
use Customize\Service\DnaQueryService;
use Eccube\Controller\AbstractController;
use Knp\Component\Pager\PaginatorInterface;
use phpDocumentor\Reflection\Types\AbstractList;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Customize\Repository\DnaSalesHeaderRepository;
use Customize\Repository\DnaSalesStatusRepository;
use Doctrine\ORM\EntityManagerInterface;

class DnaController extends AbstractController
{
    /**
     * @var DnaQueryService
     */
    protected $dnaQueryService;

    /**
     * @var DnaCheckStatusRepository
     */
    protected $dnaCheckStatusRepository;

    /**
     * @var BreederPetsRepository
     */
    protected $breederPetsRepository;

    /**
     * @var ConservationPetsRepository
     */
    protected $conservationPetsRepository;

    /**
     * @var DnaCheckKindsRepository
     */
    protected $dnaCheckKindsRepository;

    /**
     * @var DnaCheckKindsEcRepository
     */
    protected $dnaCheckKindsEcRepository;

    /**
     * @var BreedsRepository
     */
    protected $breedsRepository;

    /**
     * @var DnaCheckStatusHeaderRepository
     */
    protected $dnaCheckStatusHeaderRepository;

    /**
     * @var DnaSalesHeaderRepository
     */
    protected $dnaSalesHeaderRepository;

    /**
     * @var DnaSalesStatusRepository
     */
    protected $dnaSalesStatusRepository;

    /**
     * DnaController constructor
     * @param DnaQueryService $dnaQueryService
     * @param DnaCheckStatusRepository $dnaCheckStatusRepository
     * @param BreederPetsRepository $breederPetsRepository
     * @param ConservationPetsRepository $conservationPetsRepository
     * @param DnaCheckKindsRepository $dnaCheckKindsRepository
     * @param BreedsRepository $breedsRepository
     * @param DnaCheckStatusHeaderRepository $dnaCheckStatusHeaderRepository
     * @param DnaSalesHeaderRepository $dnaSalesHeaderRepository
     * @param DnaSalesStatusRepository $dnaSalesStatusRepository
     */
    public function __construct(
        DnaQueryService                $dnaQueryService,
        DnaCheckStatusRepository       $dnaCheckStatusRepository,
        BreederPetsRepository          $breederPetsRepository,
        ConservationPetsRepository     $conservationPetsRepository,
        DnaCheckKindsRepository        $dnaCheckKindsRepository,
        DnaCheckKindsEcRepository      $dnaCheckKindsEcRepository,
        BreedsRepository               $breedsRepository,
        DnaCheckStatusHeaderRepository $dnaCheckStatusHeaderRepository,
        DnaSalesHeaderRepository $dnaSalesHeaderRepository,
        DnaSalesStatusRepository $dnaSalesStatusRepository
    ) {
        $this->dnaQueryService = $dnaQueryService;
        $this->dnaCheckStatusRepository = $dnaCheckStatusRepository;
        $this->breederPetsRepository = $breederPetsRepository;
        $this->conservationPetsRepository = $conservationPetsRepository;
        $this->dnaCheckKindsRepository = $dnaCheckKindsRepository;
        $this->dnaCheckKindsEcRepository = $dnaCheckKindsEcRepository;
        $this->breedsRepository = $breedsRepository;
        $this->dnaCheckStatusHeaderRepository = $dnaCheckStatusHeaderRepository;
        $this->dnaSalesHeaderRepository = $dnaSalesHeaderRepository;
        $this->dnaSalesStatusRepository = $dnaSalesStatusRepository;
    }

    /**
     * DNA検査項目一覧
     *
     * @Route("/%eccube_admin_route%/dna/examination_items", name="admin_dna_examination_items")
     * @Template("@admin/DNA/examination_items.twig")
     */
    public function examination_items(PaginatorInterface $paginator, Request $request)
    {
        $dna_check_kinds = [];
        if ($request->isMethod('GET')) {
            $petType = $request->get('pet_kind');
            $breeds = $request->get('pet_breeds');
        }
        if ($request->isMethod('POST')) {
            $petType = $request->get('petType');
            $breeds = $request->get('breeds');
            $dnaCheckKind = new DnaCheckKinds();
            $breed = $this->breedsRepository->find($breeds);
            $dnaCheckKind->setCheckKind($request->get('check_kind'))
                ->setBreeds($breed)
                ->setDeleteFlg(0);
            $em = $this->getDoctrine()->getManager();
            $em->persist($dnaCheckKind);
            $em->flush();

            return $this->redirectToRoute('admin_dna_examination_items', ['pet_kind' => $petType, 'pet_breeds' => $breeds]);
        }
        $dna_check_kinds = $this->dnaCheckKindsRepository->findBy(['Breeds' => $breeds], ['update_date' => 'DESC', 'id' => 'DESC']);
        // $breedOptions = $this->breedsRepository->findBy(['pet_kind' => $petType], ['breeds_name' => 'ASC']);
        $breedOptions = $this->breedsRepository->findBy(['pet_kind' => $petType], ['sort_order' => 'ASC']);
        $dna_check_kinds = $paginator->paginate(
            $dna_check_kinds,
            $request->query->getInt('page', 1),
            AnilineConf::ANILINE_NUMBER_ITEM_PER_PAGE
        );
        return compact(
            'dna_check_kinds',
            'petType',
            'breeds',
            'breedOptions'
        );
    }

    /**
     * DNA検査項目一覧
     *
     * @Route("/%eccube_admin_route%/dna/examination_items_ec", name="admin_dna_examination_items_ec")
     * @Template("@admin/DNA/examination_items_ec.twig")
     */
    public function examination_items_ec(PaginatorInterface $paginator, Request $request)
    {
        $dna_check_kinds = [];
        if ($request->isMethod('GET')) {
            $petType = $request->get('pet_kind');
            $breeds = $request->get('pet_breeds');
        }
        if ($request->isMethod('POST')) {
            $petType = $request->get('petType');
            $breeds = $request->get('breeds');
            $dnaCheckKind = new DnaCheckKindsEc();
            $breed = $this->breedsRepository->find($breeds);
            $dnaCheckKind->setCheckKind($request->get('check_kind'))
                ->setBreeds($breed)
                ->setDeleteFlg(0);
            $em = $this->getDoctrine()->getManager();
            $em->persist($dnaCheckKind);
            $em->flush();

            return $this->redirectToRoute('admin_dna_examination_items_ec', ['pet_kind' => $petType, 'pet_breeds' => $breeds]);
        }
        $dna_check_kinds_ec = $this->dnaCheckKindsEcRepository->findBy(['Breeds' => $breeds], ['update_date' => 'DESC', 'id' => 'DESC']);
        $breedOptions = $this->breedsRepository->findBy(['pet_kind' => $petType], ['sort_order' => 'ASC']);
        $dna_check_kinds_ec = $paginator->paginate(
            $dna_check_kinds_ec,
            $request->query->getInt('page', 1),
            AnilineConf::ANILINE_NUMBER_ITEM_PER_PAGE
        );

        return compact(
            'dna_check_kinds_ec',
            'petType',
            'breeds',
            'breedOptions'
        );
    }

    /**
     * Get breeds by pet kind
     *
     * @Route("/breeds_by_pet_kind", name="breeds_by_pet_kind", methods={"GET"})
     */
    public function breedsByPetKind(Request $request, BreedsRepository $breedsRepository)
    {
        $petKind = $request->get('pet_kind');
        // $Breeds = $breedsRepository->findBy(['pet_kind' => $petKind], ['breeds_name' => 'ASC']);
        $Breeds = $breedsRepository->findBy(['pet_kind' => $petKind], ['sort_order' => 'ASC']);
        $formattedBreeds = [];
        foreach ($Breeds as $Breed) {
            $formattedBreeds[] = [
                'id' => $Breed->getId(),
                'name' => $Breed->getBreedsName()
            ];
        }

        return new JsonResponse([
            'breeds' => $formattedBreeds
        ]);
    }

    /**
     * Delete dna check kind by id
     *
     * @Route("/%eccube_admin_route%/dna/examination_items/delete", name="admin_examination_items_delete")
     */
    public function deleteExaminationItem(Request $request, DnaCheckKindsRepository $dnaCheckKindsRepository)
    {
        $id = $request->get('id');
        if (!$id || !$DnaCheckKind = $dnaCheckKindsRepository->find($id)) {
            return new JsonResponse(['isSuccess' => false], 404);
        }

        $entityManager = $this->entityManager;
        $DnaCheckKind->setDeleteFlg(!$DnaCheckKind->getDeleteFlg());
        $entityManager->flush();

        return new JsonResponse(['isSuccess' => true]);
    }

    /**
     * Delete dna check kind ec by id
     *
     * @Route("/%eccube_admin_route%/dna/examination_items_ec/delete", name="admin_examination_items_ec_delete")
     */
    public function deleteExaminationItemEc(Request $request, DnaCheckKindsEcRepository $dnaCheckKindsEcRepository)
    {
        $id = $request->get('id');
        if (!$id || !$DnaCheckKindEc = $dnaCheckKindsEcRepository->find($id)) {
            return new JsonResponse(['isSuccess' => false], 404);
        }

        $entityManager = $this->entityManager;
        $DnaCheckKindEc->setDeleteFlg(!$DnaCheckKindEc->getDeleteFlg());
        $entityManager->flush();

        return new JsonResponse(['isSuccess' => true]);
    }

    /**
     * DNA検査状況確認
     *
     * @Route("/%eccube_admin_route%/dna/examination_status", name="admin_dna_examination_status")
     * @Template("@admin/DNA/examination_status.twig")
     */
    public function examination_status(PaginatorInterface $paginator, Request $request)
    {
        if ($request->get('dna-id') && $request->isMethod('POST')) {
            $dna = $this->dnaCheckStatusRepository->find((int)$request->get('dna-id'));
            $oldDnaHeader = $dna->getDnaHeader();
            $dnaHeaders = $this->dnaCheckStatusHeaderRepository->findBy(['register_id' => $oldDnaHeader->getRegisterId(), 'shipping_status' => AnilineConf::ANILINE_SHIPPING_STATUS_ACCEPT]);
            $em = $this->getDoctrine()->getManager();
            $newDna = clone $dna;
            if (!$dnaHeaders) {
                $newDnaHeader = clone $oldDnaHeader;
                $newDnaHeader->setShippingStatus(AnilineConf::ANILINE_SHIPPING_STATUS_ACCEPT);
                $newDnaHeader->setKitUnit(1);
                $em->persist($newDnaHeader);
                $em->flush();
                $newDna->setDnaHeader($newDnaHeader);
            } else {
                $isCheckUnit = false;
                foreach ($dnaHeaders as $dnaHeader) {
                    if ($dnaHeader->getKitUnit() < AnilineConf::ANILINE_KIT_UNIT) {
                        $isCheckUnit = true;
                        $newDna->setDnaHeader($dnaHeader);
                        $dnaHeader->setKitUnit($dnaHeader->getKitUnit() + 1);
                        $em->persist($dnaHeader);
                        break;
                    }
                }

                if (!$isCheckUnit) {
                    $newDnaHeader = clone $oldDnaHeader;
                    $newDnaHeader->setShippingStatus(AnilineConf::ANILINE_SHIPPING_STATUS_ACCEPT);
                    $newDnaHeader->setKitUnit(1);
                    $em->persist($newDnaHeader);
                    $em->flush();
                    $newDna->setDnaHeader($newDnaHeader);
                }
            }
            $newDna->setCheckStatus(AnilineConf::ANILINE_DNA_CHECK_STATUS_SHIPPING);
            $dna->setCheckStatus(AnilineConf::ANILINE_DNA_CHECK_STATUS_RESENT);
            $em->persist($newDna);
            $em->persist($dna);
            $em->flush();

            return $this->redirectToRoute('admin_dna_examination_status');
        }

        if ($request->get('change-status-id') && $request->isMethod('POST')) {
            $dna = $this->dnaCheckStatusRepository->find($request->get('change-status-id'));
            //$dna->setCheckStatus(AnilineConf::ANILINE_DNA_CHECK_STATUS_PUBLIC);
            if ($dna->getSiteType() == AnilineConf::ANILINE_SITE_TYPE_BREEDER) {
                $pet = $this->breederPetsRepository->find($dna->getPetId());
                $pet->setIsActive(AnilineConf::ANILINE_IS_ACTIVE_PUBLIC)
                    ->setReleaseDate(Carbon::now());
            } else {
                $pet = $this->conservationPetsRepository->find($dna->getPetId());
            }
            $em = $this->getDoctrine()->getManager();
            $em->persist($pet);
            $em->persist($dna);
            $em->flush();

            return $this->redirectToRoute('admin_dna_examination_status');
        }

        $criteria = [];

        if ($request->get('customer_name')) {
            $criteria['customer_name'] = $request->get('customer_name');
        }

        switch ($request->get('pet_kind')) {
            case 1:
                $criteria['pet_kind'] = AnilineConf::ANILINE_PET_KIND_DOG;
                break;
            case 2:
                $criteria['pet_kind'] = AnilineConf::ANILINE_PET_KIND_CAT;
                break;
            default:
                break;
        }

        if ($request->get('check_status')) {
            $criteria['check_status'] = $request->get('check_status');
        }

        if ($request->get('kit_regist_date_from')) {
            $criteria['kit_regist_date_from'] = $request->get('kit_regist_date_from');
        }
        if ($request->get('kit_regist_date_to')) {
            $criteria['kit_regist_date_to'] = $request->get('kit_regist_date_to');
        }
        if ($request->get('kit_return_date_from')) {
            $criteria['kit_return_date_from'] = $request->get('kit_return_date_from');
        }
        if ($request->get('kit_return_date_to')) {
            $criteria['kit_return_date_to'] = $request->get('kit_return_date_to');
        }
        if ($request->get('check_return_date_from')) {
            $criteria['check_return_date_from'] = $request->get('check_return_date_from');
        }
        if ($request->get('check_return_date_to')) {
            $criteria['check_return_date_to'] = $request->get('check_return_date_to');
        }
        $isDelete = [];
        $results = $this->dnaQueryService->filterDnaAdmin($criteria);
        foreach ($results as $result) {
            $isDelete[$result['dna_id']] = null;
            if ($result['pet_id']) {
                $pet = $this->breederPetsRepository->find($result['pet_id']);
                $isDelete[$result['dna_id']] = $pet->getIsDelete();
            }
        }

        $dnas = $paginator->paginate(
            $results,
            $request->query->getInt('page', 1),
            $request->query->getInt('item', 50)
        );

        return [
            'dnas' => $dnas,
            'isDelete' => $isDelete
        ];
    }

    /**
     * DNA検査状況確認（有料）
     *
     * @Route("/%eccube_admin_route%/dna/examination_status_ec", name="admin_dna_examination_status_ec")
     * @Template("@admin/DNA/examination_status_ec.twig")
     */
    public function examination_status_ec(PaginatorInterface $paginator, Request $request)
    {

        // ログインユーザーの定期注文を全取得
        $results = $this->dnaSalesStatusRepository->createQueryBuilder('ds')
            ->Where('ds.check_status > 0')
            ->orderBy("ds.id",  "DESC")
            ->getQuery()->getResult();

        $dnas = $paginator->paginate(
            $results,
            $request->query->getInt('page', 1),
            $request->query->getInt('item', 50)
        );

        return [
            'dnas' => $dnas
        ];
    }


    /**
     * CSVダウンロード
     *
     * @Route("/%eccube_admin_route%/dna/get_csvlist", name="admin_dna_get_csvlist")
     * 
     */
    public function getCsvList(Request $request)
    {
        $filename = 'dna_statuslist_'.(new \DateTime())->format('YmdHis').'.csv';
        $filePath = 'var/dna_statuslist.csv';
        
        $response = new StreamedResponse();

        $response->setCallback(function () use ($request) {
            $fp = fopen('php://output', 'w');

            $headers = mb_convert_encoding("id,ブリーダー名,犬種・猫種,キット出荷日,結果受信日,検査機関\r\n","SJIS");
            fputs($fp,$headers);
            $criteria = [];

            $dna_result = array("未決定","VEQTA","ラボ");
            $results = $this->dnaQueryService->filterDnaAdmin($criteria);
            foreach($results as $result){
                $row = array();

                $row[] = $result["dna_id"];
                $row[] =  mb_convert_encoding($result["name01"]. " " .$result["name02"],"SJIS");
                $row[] =  mb_convert_encoding($result["breeds_name"],"SJIS");
                if($result["kit_shipping_date"]){
                    $row[] =  mb_convert_encoding($result["kit_shipping_date"]->format('Y/m/d H:i:s'),"SJIS");
                }
                else{
                    $row[] = "";
                }
                if($result["kit_return_date"]){
                    $row[] =  mb_convert_encoding($result["kit_return_date"]->format('Y/m/d H:i:s'),"SJIS");
                }
                else{
                    $row[] = "";
                }
                $row[] =  mb_convert_encoding($dna_result[$result["labo_type"]],"SJIS");
                
                fputcsv($fp,$row);
            }

            fclose($fp);
        });

        $response->headers->set('Content-Type', 'application/octet-stream');
        $response->headers->set('Content-Disposition', 'attachment; filename=' . $filename);

        $response->send();

        return $response;
    }

    /**
     * Download PDF
     *
     * @Route("/%eccube_admin_route%/dna/examination_status/download_pdf/{id}", requirements={"id" = "\d+"}, name="admin_dna_download_pdf")
     *
     * @param DnaCheckStatus $dnaCheckStatus
     * @return BinaryFileResponse
     */
    public function download(DnaCheckStatus $dnaCheckStatus): BinaryFileResponse
    {
        if (!$pdfPath = $dnaCheckStatus->getFilePath()) {
            throw new NotFoundHttpException("Pdf DNA not found!");
        }
        $nameArr = explode("/", $pdfPath);
        $fileName = end($nameArr);
        $response = new BinaryFileResponse($pdfPath);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $fileName);

        return $response;
    }

    /**
     * DNA検査状況確認
     *
     * @Route("/%eccube_admin_route%/dna/examination_headers", name="admin_dna_examination_headers")
     * @Template("@admin/DNA/examination_headers.twig")
     */
    public function examination_headers(PaginatorInterface $paginator, Request $request)
    {
        $results = $this->dnaCheckStatusHeaderRepository->findBy(["labo_type" => 0], array('id' => 'DESC'));

        $headers = $paginator->paginate(
            $results,
            $request->query->getInt('page', 1),
            $request->query->getInt('item', 50)
        );
        return [
            'headers' => $headers
        ];
    }

    /**
     * 検査機関設定実行
     *
     * @Route("/%eccube_admin_route%/dna/examination_headers/execute", name="admin_dna_examination_headers_execute")
     *
     */
    public function admin_dna_examination_headers_execute(Request $request)
    {
        $id = $request->request->get("header-id");
        $labo_type = $request->request->get("labo-type");

        $header = $this->dnaCheckStatusHeaderRepository->find($id);
        $header->setLaboType($labo_type);
        $this->entityManager->persist($header);
        $this->entityManager->flush();

        return $this->redirectToRoute('admin_dna_examination_headers');
    }
}
