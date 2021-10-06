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

namespace Customize\Controller\Admin;

use Customize\Config\AnilineConf;
use Customize\Form\Type\Admin\ConservationHouseType;
use Customize\Repository\BreedsRepository;
use Customize\Repository\ConservationPetsRepository;
use Customize\Repository\ConservationsRepository;
use Customize\Entity\Conservations;
use Customize\Repository\CoatColorsRepository;
use Customize\Repository\ConservationPetImageRepository;
use Customize\Form\Type\Admin\ConservationsType;
use Customize\Service\AdoptionQueryService;
use Customize\Service\MailService;
use Eccube\Controller\AbstractController;
use Eccube\Repository\CustomerRepository;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception as HttpException;

class AdoptionController extends AbstractController
{
    /**
     * @var ConservationsRepository
     */
    protected $conservationsRepository;

    /**
     * @var ConservationPetsRepository;
     */
    protected $conservationPetsRepository;

    /**
     * @var BreedsRepository;
     */
    protected $breedsRepository;

    /**
     * @var AdoptionQueryService;
     */
    protected $adoptionQueryService;

    /**
     * @var CoatColorsRepository
     */
    protected $coatColorsRepository;

    /**
     * @var ConservationPetImageRepository
     */
    protected $conservationPetImageRepository;

    /**
     * @var CustomerRepository
     */
    protected $customerRepository;

    /**
     * @var MailService
     */
    protected $mailService;

    /**
     * AdoptionController constructor.
     *
     * @param ConservationsRepository $conservationsRepository
     * @param BreedsRepository $breedsRepository
     * @param CoatColorsRepository $coatColorsRepository
     * @param ConservationPetImageRepository $conservationPetImageRepository
     * @param ConservationPetsRepository $conservationPetsRepository
     * @param AdoptionQueryService $adoptionQueryService
     * @param CustomerRepository $customerRepository
     * @param MailService $mailService
     */

    public function __construct(
        ConservationsRepository        $conservationsRepository,
        BreedsRepository               $breedsRepository,
        CoatColorsRepository           $coatColorsRepository,
        ConservationPetImageRepository $conservationPetImageRepository,
        ConservationPetsRepository     $conservationPetsRepository,
        AdoptionQueryService           $adoptionQueryService,
        CustomerRepository             $customerRepository,
        MailService                    $mailService
    ) {
        $this->conservationsRepository = $conservationsRepository;
        $this->breedsRepository = $breedsRepository;
        $this->coatColorsRepository = $coatColorsRepository;
        $this->conservationPetImageRepository = $conservationPetImageRepository;
        $this->conservationPetsRepository = $conservationPetsRepository;
        $this->adoptionQueryService = $adoptionQueryService;
        $this->customerRepository = $customerRepository;
        $this->mailService = $mailService;
    }

    /**
     * 保護団体一覧
     *
     * @Route("/%eccube_admin_route%/adoption/adoption_list", name="admin_adoption_list")
     * @Template("@admin/Adoption/index.twig")
     */
    public function index(PaginatorInterface $paginator, Request $request)
    {
        $request = $request->query->all();
        $results = $this->conservationsRepository->searchConservations($request);
        $conservations = $paginator->paginate(
            $results,
            $request['page'] ?? 1,
            AnilineConf::ANILINE_NUMBER_ITEM_PER_PAGE
        );

        return $this->render('@admin/Adoption/index.twig', [
            'conservations' => $conservations,
            'direction' => !isset($request['direction']) || $request['direction'] === 'DESC' ? 'ASC' : 'DESC',
        ]);
    }

    /**
     * 登録内容編集保護団体管理
     *
     * @Route("/%eccube_admin_route%/adoption/edit/{id}", name="admin_adoption_edit", requirements={"id" = "\d+"})
     * @Template("@admin/Adoption/edit.twig")
     */
    public function Edit(Request $request, Conservations $conservations)
    {
        $form = $this->createForm(ConservationsType::class, $conservations);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($conservations);
            $entityManager->flush();
            return $this->redirectToRoute('admin_adoption_list');
        }
        return $this->render('@admin/Adoption/edit.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * 犬舎・猫舎情報編集保護団体管理
     *
     * @Route("/%eccube_admin_route%/adoption/house/{id}", name="admin_adoption_house", requirements={"id" = "\d+"})
     * @Template("@admin/Adoption/house.twig")
     */
    public function House(Request $request, Conservations $conservations)
    {
        $conservationsHouse = null;
        $conservationsHouses = $conservations->getConservationsHouses();
        if (!$conservationsHouses->isEmpty()) {
            $conservationsHouse = $conservationsHouses->first();
        }
        if ($request->get('pet_type')) {
            $conservationsHouse = $conservations->getConservationHouseByPetType($request->query->getInt('pet_type'));
        }
        if (!$conservationsHouse || !$conservationsHouse->getId()) {
            throw new HttpException\NotFoundHttpException();
        }

        $form = $this->createForm(ConservationHouseType::class, $conservationsHouse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $conservationsHouse->setConservationHousePref($conservationsHouse->getPref());
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($conservationsHouse);
            $entityManager->flush();
            return $this->redirectToRoute('admin_adoption_list');
        }
        return $this->render('@admin/Adoption/house.twig', [
            'conservations' => $conservations,
            'form' => $form->createView()
        ]);
    }
}
