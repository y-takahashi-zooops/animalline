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

use Customize\Config\AnilineConf;
use Eccube\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Exception;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Knp\Component\Pager\PaginatorInterface;
use Customize\Repository\DnaCheckStatusHeaderRepository;
use Customize\Repository\DnaCheckStatusRepository;

class UlogiController extends AbstractController
{

    /**
     * @var DnaCheckStatusHeaderRepository
     */
    protected $dnaCheckStatusHeaderRepository;

    /**
     * @var DnaCheckStatusRepository
     */
    protected $dnaCheckStatusRepository;


    /**
     * VeqtaController constructor.
     * @param DnaCheckStatusHeaderRepository $dnaCheckStatusHeaderRepository
     * @param DnaCheckStatusRepository $dnaCheckStatusRepository
     */
    public function __construct(
        DnaCheckStatusHeaderRepository   $dnaCheckStatusHeaderRepository,
        DnaCheckStatusRepository         $dnaCheckStatusRepository
    ) {
        $this->dnaCheckStatusHeaderRepository = $dnaCheckStatusHeaderRepository;
        $this->dnaCheckStatusRepository = $dnaCheckStatusRepository;
    }

    /**
     * Register and receive DNA kit result
     *
     * @Route("/ulogi/", name="ulogi_index")
     * @Template("animalline/ulogi/index.twig")
     */
    public function index(): array
    {
        return [];
    }

    /**
     * Register and receive DNA kit result
     *
     * @Route("/ulogi/print_list", name="ulogi_print_list")
     * @Template("animalline/ulogi/print_list.twig")
     */
    public function print_list(Request $request): array
    {
        $registerId = $this->getUser();

        $dnas = $this->dnaCheckStatusHeaderRepository->findBy(
            ['shipping_status' => AnilineConf::ANILINE_SHIPPING_STATUS_INSTRUCTING],
            ['create_date' => 'DESC']
        );

        return ['dnas' => $dnas,];
    }

    /**
     * Register and receive DNA kit result
     *
     * @Route("/ulogi/get_printdata_kit_barcode", name="ulogi_get_printdata_kit_barcode")
     */
    public function get_printdata_kit_barcode(Request $request)
    {
        $registerId = $this->getUser();
        
        $header = $this->dnaCheckStatusHeaderRepository->findOneBy(['id' =>$request->get("header_id")]);

        $dnas = $this->dnaCheckStatusRepository->findBy(
            ['DnaHeader' => $header],
            ['id' => 'ASC']
        );

        $i=1;
        foreach($dnas as $dna){
            $datas[$i] = [
                'barcode' =>  $dna->getSiteType().sprintf('%05d',$dna->getId())
            ];

            $i++;
        }
        return $this->json([
            'title' => $header->getShippingName(),
            'datas' => $datas,
        ]);
    }

    /**
     * Register and receive DNA kit result
     *
     * @Route("/ulogi/get_printdata_kit_sheet", name="ulogi_get_printdata_kit_sheet")
     */
    public function get_printdata_kit_sheet(Request $request)
    {
        $registerId = $this->getUser();
        
        $header = $this->dnaCheckStatusHeaderRepository->findOneBy(['id' =>$request->get("header_id")]);

        return $this->json([
            'post_code' => "〒".substr($header->getShippingZip(),0,3) . "-" . substr($header->getShippingZip(),3),
            'address01' => $header->getShippingPref().$header->getShippingCity(),
            'address02' => $header->getShippingAddress(),
            'name' => $header->getShippingName(),
        ]);
    }

    /**
     * Register and receive DNA kit result
     *
     * @Route("/ulogi/print_confirm_barcode", name="ulogi_print_confirm_barcode")
     * @Template("animalline/ulogi/print_confirm_barcode.twig")
     */
    public function print_confirm_barcode(Request $request)
    {
        $fileid = $request->get("fileid");

        return $this->render("animalline/ulogi/print_confirm_barcode.twig", [
            'fileid' => $fileid
        ]);
    }

    /**
     * Register and receive DNA kit result
     *
     * @Route("/ulogi/print_confirm_sheet", name="ulogi_print_confirm_sheet")
     * @Template("animalline/ulogi/print_confirm_sheet.twig")
     */
    public function print_confirm_sheet(Request $request)
    {
        $fileid = $request->get("fileid");

        return $this->render("animalline/ulogi/print_confirm_sheet.twig", [
            'fileid' => $fileid
        ]);
    }

}
