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

use Customize\Repository\DnaCheckStatusHeaderRepository;
use Carbon\Carbon;
use Customize\Config\AnilineConf;
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
     * VeqtaController constructor.
     *
     * @param DnaCheckStatusHeaderRepository $dnaCheckStatusHeaderRepository
     * @param BreederPetsRepository $breederPetsRepository
     * @param ConservationPetsRepository $conservationPetsRepository
     * @param DnaCheckStatusRepository $dnaCheckStatusRepository
     */

    public function __construct(
        DnaCheckStatusHeaderRepository $dnaCheckStatusHeaderRepository,
        BreederPetsRepository $breederPetsRepository,
        ConservationPetsRepository $conservationPetsRepository,
        DnaCheckStatusRepository $dnaCheckStatusRepository
    ) {
        $this->dnaCheckStatusHeaderRepository = $dnaCheckStatusHeaderRepository;
        $this->breederPetsRepository = $breederPetsRepository;
        $this->conservationPetsRepository = $conservationPetsRepository;
        $this->dnaCheckStatusRepository = $dnaCheckStatusRepository;
    }

    /**
     * Register and receive DNA kit result
     *
     * @Route("/veqta/", name="veqta_index")
     * @Template("animalline/veqta/index.twig")
     */
    public function index()
    {
        return [];
    }

    /**
     * Pet list
     *
     * @Route("/veqta/pet_list", name="veqta_pet_list")
     * @Template("animalline/veqta/pet_list.twig")
     */
    public function pet_list()
    {
        return [];
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
        $name = null;
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
     * @Route("/veqta/result", name="veqta_result")
     * @Template("animalline/veqta/result.twig")
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
            case AnilineConf::ANILINE_DNA_CHECK_STATUS_SPECIMEN_ABNORMALITY: {
                    $Dna->setCheckStatus($checkStatus);
                    break;
                }
            case AnilineConf::ANILINE_DNA_CHECK_STATUS_TEST_NG: {
                    $Dna->setCheckStatus($checkStatus);
                    $Pet->setDnaCheckResult(AnilineConf::DNA_CHECK_RESULT_3);
                    break;
                }
            default: {
                    $Dna->setCheckStatus(AnilineConf::ANILINE_DNA_CHECK_STATUS_PASSED);
                    $Pet->setDnaCheckResult($checkStatus == 61 ? AnilineConf::DNA_CHECK_RESULT_1 : AnilineConf::DNA_CHECK_RESULT_2); // 61: クリア, 62: キャリア.
                }
        }

        $savePath = $this->copyFile($request->get('file_name'));
        $Dna->setFilePath($savePath);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($Dna);
        $entityManager->persist($Pet);
        $entityManager->flush();

        return;
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
        copy($fromPath, $toPath); // ? should be move instead of copy.

        return $toPath;
    }

    /**
     * Upload file.
     * @Route("/upload_file", name="upload_file", methods={"POST"})
     */
    public function uploadFile(Request $request)
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
     * @Route("/read_barcode", name="read_barcode", methods={"GET"})
     */
    public function readBarCode(Request $request)
    {
        $barcode = $request->get('barcode');
        $siteType = $barcode[0];
        $dnaId = substr($barcode, 1);

        $Dna = $this->dnaCheckStatusRepository->findOneBy(['id' => $dnaId, 'site_type' => $siteType]);
        $data['shippingName'] = $Dna->getDnaHeader()->getShippingName();
        $data['hasRecord'] = $Dna->getCheckStatus() === AnilineConf::ANILINE_DNA_CHECK_STATUS_CHECKING;

        return new JsonResponse($data);
    }
}
