<?php

namespace Customize\Controller\Admin;

use Customize\Config\AnilineConf;
use Customize\Repository\DnaCheckStatusRepository;
use Customize\Service\DnaQueryService;
use Eccube\Controller\AbstractController;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
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
     *
     */
    public function __construct(
        DnaQueryService          $dnaQueryService,
        DnaCheckStatusRepository $dnaCheckStatusRepository
    )
    {
        $this->dnaQueryService = $dnaQueryService;
        $this->dnaCheckStatusRepository = $dnaCheckStatusRepository;
    }

    /**
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
}
