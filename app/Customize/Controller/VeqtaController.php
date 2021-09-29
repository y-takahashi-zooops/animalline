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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Eccube\Form\Type\Front\ContactType;
use Exception;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class VeqtaController extends AbstractController
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
     *
     * @param DnaCheckStatusHeaderRepository $dnaCheckStatusHeaderRepository
     *  @param DnaCheckStatusrRepository $dnaCheckStatusrRepository
     */

    public function __construct(
        DnaCheckStatusHeaderRepository $dnaCheckStatusHeaderRepository,
        DnaCheckStatusRepository $dnaCheckStatusRepository
    ) {
        $this->dnaCheckStatusHeaderRepository = $dnaCheckStatusHeaderRepository;
        $this->dnaCheckStatusRepository = $dnaCheckStatusRepository;
    }

    /**
     * @Route("/veqta/", name="veqta_index")
     * @Template("animalline/veqta/index.twig")
     */
    public function index()
    {
        return [];
    }

    /**
     * @Route("/veqta/arrive", name="veqta_arrive")
     * @Template("animalline/veqta/arrive.twig")
     */
    public function arrive()
    {
        return [];
    }

    /**
     * Dna result regist.
     * @Route("/veqta/result", name="veqta_result")
     * @Template("animalline/veqta/result.twig")
     */
    public function result(Request $request, BreederPetsRepository $breederPetsRepository, ConservationPetsRepository $conservationPetsRepository, DnaCheckStatusRepository $dnaCheckStatusRepository)
    {
        $barCode = $request->get('barcode');
        if ($request->isMethod('POST')) {
            $barcode = $request->get('barcode');
            $checkStatus = $request->get('check_status');


            $siteType = $barcode[0];
            $dnaId = substr($barcode, 1);
            $Dna = $dnaCheckStatusRepository->findOneBy(['id' => $dnaId, 'site_type' => $siteType]);
            if (!$Dna) {
                throw new NotFoundHttpException();
            }
            $Pet = $siteType == AnilineConf::ANILINE_SITE_TYPE_BREEDER ?
                $breederPetsRepository->find($Dna->getPetId()) :
                $conservationPetsRepository->find($Dna->getPetId());
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
                        $Dna->setCheckStatus(AnilineConf::ANILINE_DNA_CHECK_STATUS_NOT_NORMAL);
                        $Pet->setDnaCheckResult($checkStatus == 61 ? AnilineConf::DNA_CHECK_RESULT_1 : AnilineConf::DNA_CHECK_RESULT_2); // 61: ???, 62: ????.
                        $Pet->setReleaseStatus(1);
                        $Pet->setReleaseDate(Carbon::now());
                    }
            }

            $savePath = $this->copyFile($request->get('file_name'));
            $Dna->setFilePath($savePath);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($Dna);
            $entityManager->persist($Pet);
            $entityManager->flush();
        }

		$barCode = $request->get('barCode');
        $dnaCheckStatusId = (int)substr($barCode, 1);
        $siteType = (int)substr($barCode, 0,1);
        $shippingName = null;

        $dnaCheckStatus = $this->dnaCheckStatusRepository->findBy(['id' => $dnaCheckStatusId, 'site_type' => $siteType]);

        if ($dnaCheckStatus) {
            $dnaCheckStatusHeader = $this->dnaCheckStatusHeaderRepository->find($dnaCheckStatus[0]->getDnaHeader());
            if ($dnaCheckStatus[0]->getCheckStatus() == 5) {
                    $shippingName = $dnaCheckStatusHeader->getShippingName();
                    return new JsonResponse(array(['shippingName' => $shippingName]));
            }
        }
        return [];
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

        $toFolder = AnilineConf::ANILINE_IMAGE_URL_BASE . '/lisence/';
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
}
