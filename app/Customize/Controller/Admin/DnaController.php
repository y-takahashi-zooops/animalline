<?php

namespace Customize\Controller\Admin;

use Customize\Config\AnilineConf;
use Customize\Entity\DnaCheckStatus;
use Customize\Repository\DnaCheckStatusRepository;
use Customize\Service\DnaQueryService;
use Eccube\Controller\AbstractController;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
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
     * DnaController constructor
     * @param DnaQueryService $dnaQueryService
     * @param DnaCheckStatusRepository $dnaCheckStatusRepository
     */
    public function __construct(
        DnaQueryService          $dnaQueryService,
        DnaCheckStatusRepository $dnaCheckStatusRepository
    ) {
        $this->dnaQueryService = $dnaQueryService;
        $this->dnaCheckStatusRepository = $dnaCheckStatusRepository;
    }

    /**
     * 検査状況確認DNA検査
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
            $newDna->setCheckStatus(AnilineConf::ANILINE_DNA_CHECK_STATUS_DEFAULT);
            $em = $this->getDoctrine()->getManager();
            $em->persist($newDna);
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
