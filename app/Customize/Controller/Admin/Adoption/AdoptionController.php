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

namespace Customize\Controller\Admin\Adoption;

use Customize\Config\AnilineConf;
use Customize\Repository\BreedsRepository;
use Customize\Repository\ConservationPetsRepository;
use Customize\Repository\ConservationsRepository;
use Customize\Entity\Conservations;
use Customize\Repository\ConservationPetImageRepository;
use Customize\Form\Type\Adoption\ConservationsType;
use Customize\Repository\ConservationBankAccountRepository;
use Customize\Service\AdoptionQueryService;
use Customize\Service\MailService;
use Eccube\Controller\AbstractController;
use Eccube\Repository\CustomerRepository;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

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
     * @var BreedsRepository
     */
    protected $breedsRepository;

    /**
     * @var AdoptionQueryService
     */
    protected $adoptionQueryService;

    /**
     * @var ConservationPetImageRepository
     */
    protected $conservationPetImageRepository;

    /**
     * @var CustomerRepository
     */
    protected $customerRepository;

    /**
     * @var ConservationBankAccountRepository
     */
    protected $conservationBankAccountRepository;

    /**
     * @var MailService
     */
    protected $mailService;

    /**
     * AdoptionController constructor.
     *
     * @param ConservationsRepository $conservationsRepository
     * @param BreedsRepository $breedsRepository
     * @param ConservationPetImageRepository $conservationPetImageRepository
     * @param ConservationPetsRepository $conservationPetsRepository
     * @param AdoptionQueryService $adoptionQueryService
     * @param CustomerRepository $customerRepository
     * @param ConservationBankAccountRepository $conservationBankAccountRepository
     * @param MailService $mailService
     */

    public function __construct(
        ConservationsRepository        $conservationsRepository,
        BreedsRepository               $breedsRepository,
        ConservationPetImageRepository $conservationPetImageRepository,
        ConservationPetsRepository     $conservationPetsRepository,
        AdoptionQueryService           $adoptionQueryService,
        CustomerRepository             $customerRepository,
        ConservationBankAccountRepository             $conservationBankAccountRepository,
        MailService                    $mailService
    ) {
        $this->conservationsRepository = $conservationsRepository;
        $this->breedsRepository = $breedsRepository;
        $this->conservationPetImageRepository = $conservationPetImageRepository;
        $this->conservationPetsRepository = $conservationPetsRepository;
        $this->adoptionQueryService = $adoptionQueryService;
        $this->customerRepository = $customerRepository;
        $this->conservationBankAccountRepository = $conservationBankAccountRepository;
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

        $adoptionstatus[0] = "申請待ち";
        $adoptionstatus[1] = "未審査";
        $adoptionstatus[2] = "審査済（許可）";
        $adoptionstatus[3] = "審査済（拒否）";
        $adoptionstatus[4] = "取消済";

        return $this->render('@admin/Adoption/index.twig', [
            'conservations' => $conservations,
            'direction' => !isset($request['direction']) || $request['direction'] === 'DESC' ? 'ASC' : 'DESC',
            'adoptionstatus' => $adoptionstatus,
        ]);
    }

    /**
     * 登録内容編集保護団体管理
     *
     * @Route("/%eccube_admin_route%/adoption/edit/{id}", name="admin_adoption_edit", requirements={"id" = "\d+"})
     * @Template("@admin/Adoption/edit.twig")
     */
    public function Edit(Request $request, Conservations $conservation)
    {
        $form = $this->createForm(ConservationsType::class, $conservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $conservation->setPref($conservation->getPrefId());
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($conservation);
            $entityManager->flush();
            return $this->redirectToRoute('admin_adoption_list');
        }
        return $this->render('@admin/Adoption/edit.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * 銀行口座情報
     *
     * @Route("/%eccube_admin_route%/adoption/bank_account/{id}", name="admin_adoption_bank_account", requirements={"id" = "\d+"})
     * @Template("@admin/Adoption/bank_account.twig")
     */
    public function bankAccount(Request $request): array
    {
        if (!$BankAccount = $this->conservationBankAccountRepository->find($request->get('id'))) {
            throw new NotFoundHttpException();
        }

        return [
            'BankAccount' => $BankAccount
        ];
    }
}
