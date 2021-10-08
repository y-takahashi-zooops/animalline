<?php

namespace Customize\Controller\Admin\DNA;

use Carbon\Carbon;
use Customize\Config\AnilineConf;
use Customize\Repository\BreederPetsRepository;
use Customize\Repository\ConservationPetsRepository;
use Customize\Entity\DnaCheckStatus;
use Customize\Repository\BreedsRepository;
use Customize\Repository\DnaCheckKindsRepository;
use Customize\Repository\DnaCheckStatusRepository;
use Customize\Service\DnaQueryService;
use Eccube\Controller\AbstractController;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class DnaController extends AbstractController
{
    /**
     * @var DnaQueryService;
     */
    protected $dnaQueryService;

    /**
     * @var DnaCheckStatusRepository;
     */
    protected $dnaCheckStatusRepository;

    /**
     * @var BreederPetsRepository;
     */
    protected $breederPetsRepository;

    /**
     * @var ConservationPetsRepository;
     */
    protected $conservationPetsRepository;

    /**
     * @var DnaCheckKindsRepository;
     */
    protected $dnaCheckKindsRepository;

    /**
     * DnaController constructor
     * @param DnaQueryService $dnaQueryService
     * @param DnaCheckStatusRepository $dnaCheckStatusRepository
     * @param BreederPetsRepository $breederPetsRepository
     * @param ConservationPetsRepository $conservationPetsRepository
     * @param DnaCheckKindsRepository $dnaCheckKindsRepository
     */
    public function __construct(
        DnaQueryService            $dnaQueryService,
        DnaCheckStatusRepository   $dnaCheckStatusRepository,
        BreederPetsRepository      $breederPetsRepository,
        ConservationPetsRepository $conservationPetsRepository,
        DnaCheckKindsRepository    $dnaCheckKindsRepository
    )
    {
        $this->dnaQueryService = $dnaQueryService;
        $this->dnaCheckStatusRepository = $dnaCheckStatusRepository;
        $this->breederPetsRepository = $breederPetsRepository;
        $this->conservationPetsRepository = $conservationPetsRepository;
        $this->dnaCheckKindsRepository = $dnaCheckKindsRepository;
    }

    /**
     * DNA検査項目一覧
     *
     * @Route("/%eccube_admin_route%/dna/examination_items", name="admin_dna_examination_items")
     * @Template("@admin/DNA/examination_items.twig")
     */
    public function examination_items(Request $request)
    {
        $dna_check_kinds = [];
        $checkKind = $request->get('pet_kind');
        $breeds = $request->get('pet_breeds');
        if ($request->isMethod('GET')) {
            $dna_check_kinds = $this->dnaCheckKindsRepository->findBy(['check_kind' => $request->get('pet_kind'), 'Breeds' => $request->get('pet_breeds')]);
        }
        return compact(
            'dna_check_kinds',
            'checkKind',
            'breeds'
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
        $Breeds = $breedsRepository->findBy(['pet_kind' => $petKind]);
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
        if(!$id || !$DnaCheckKind = $dnaCheckKindsRepository->find($id)) {
            return new JsonResponse(['isSuceess' => false], 404);
        }

        $entityManager = $this->getDoctrine()->getManager();
        $DnaCheckKind->setDeleteFlg(!$DnaCheckKind->getDeleteFlg());
        $entityManager->flush();

        return new JsonResponse(['isSuceess' => true]);
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
            $dna->setCheckStatus(AnilineConf::ANILINE_DNA_CHECK_STATUS_RESENT);
            $newDna = clone $dna;
            $newDna->setCheckStatus(AnilineConf::ANILINE_DNA_CHECK_STATUS_SHIPPING);
            $em = $this->getDoctrine()->getManager();
            $em->persist($newDna);
            $em->flush();

            return $this->redirectToRoute('admin_dna_examination_status');
        }

        if ($request->get('change-status-id') && $request->isMethod('POST')) {
            $dna = $this->dnaCheckStatusRepository->find($request->get('change-status-id'));
            $dna->setCheckStatus(AnilineConf::ANILINE_DNA_CHECK_STATUS_PUBLIC);
            if ($dna->getSiteType() == AnilineConf::ANILINE_SITE_TYPE_BREEDER) {
                $pet = $this->breederPetsRepository->find($dna->getPetId());
            } else {
                $pet = $this->conservationPetsRepository->find($dna->getPetId());
            }
            $em = $this->getDoctrine()->getManager();
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

        $results = $this->dnaQueryService->filterDnaAdmin($criteria);
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
}
