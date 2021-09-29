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
use Customize\Repository\DnaCheckStatusRepository;
use Eccube\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Eccube\Form\Type\Front\ContactType;
use Customize\Entity\DnaCheckStatusHeader;
use Customize\Repository\DnaCheckStatusHeaderRepository;

class VeqtaController extends AbstractController
{
    /**
     * @var DnaCheckStatusRepository;
     */
    protected $dnaCheckStatusRepository;

    /**
     * @var DnaCheckStatusHeaderRepository
     */
    protected $dnaCheckStatusHeaderRepository;


    /**
     * Import dna check status constructor.
     *
     * @param DnaCheckStatusHeaderRepository $dnaCheckStatusHeaderRepository
     * @param DnaCheckStatusRepository $dnaCheckStatusRepository
     *
     */
    public function __construct(
        DnaCheckStatusHeaderRepository $dnaCheckStatusHeaderRepository,
        DnaCheckStatusRepository       $dnaCheckStatusRepository
    ) {
        $this->dnaCheckStatusHeaderRepository = $dnaCheckStatusHeaderRepository;
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
     * @Route("/veqta/result", name="veqta_result")
     * @Template("animalline/veqta/result.twig")
     */
    public function result()
    {
        return [];
    }
}
