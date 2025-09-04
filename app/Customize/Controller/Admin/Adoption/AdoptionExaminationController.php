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
use Customize\Entity\Conservations;
use Customize\Form\Type\Adoption\ConservationHouseType;
use Customize\Form\Type\Adoption\ConservationsType;
use Customize\Repository\ConservationsRepository;
use Customize\Service\MailService;
use Eccube\Controller\AbstractController;
use Eccube\Repository\CustomerRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Doctrine\ORM\EntityManagerInterface;

class AdoptionExaminationController extends AbstractController
{
    /**
     * @var ConservationsRepository
     */
    protected $conservationsRepository;

    /**
     * @var CustomerRepository
     */
    protected $customerRepository;

    /**
     * @var MailService
     */
    protected $mailService;

    /**
     * AdoptionExaminationController constructor.
     *
     * @param ConservationsRepository $conservationsRepository
     * @param CustomerRepository $customerRepository
     * @param MailService $mailService
     */

    public function __construct(
        ConservationsRepository $conservationsRepository,
        CustomerRepository      $customerRepository,
        MailService             $mailService
    ) {
        $this->conservationsRepository = $conservationsRepository;
        $this->customerRepository = $customerRepository;
        $this->mailService = $mailService;
    }

    /**
     * 審査情報表示保護団体管理
     *
     * @Route("/%eccube_admin_route%/adoption/examination/{id}", name="admin_adoption_examination", requirements={"id" = "\d+"})
     * @Template("@admin/Adoption/examination.twig")
     */
    public function Examination(Conservations $conservations): array
    {
        $formAdoption = $this->createForm(ConservationsType::class, $conservations, ['disabled' => true]);

        $conservationsHouseDog = $conservations->getConservationHouseByPetType(AnilineConf::ANILINE_PET_KIND_DOG);
        $formHouseDog = $this->createForm(ConservationHouseType::class, $conservationsHouseDog, ['disabled' => true]);

        $conservationsHouseCat = $conservations->getConservationHouseByPetType(AnilineConf::ANILINE_PET_KIND_CAT);
        $formHouseCat = $this->createForm(ConservationHouseType::class, $conservationsHouseCat, ['disabled' => true]);

        return [
            'formAdoption' => $formAdoption->createView(),
            'formHouseDog' => $conservationsHouseDog->getId() ? $formHouseDog->createView() : false,
            'formHouseCat' => $conservationsHouseCat->getId() ? $formHouseCat->createView() : false,
            'conservation' => $conservations
        ];
    }

    /**
     * 審査結果登録保護団体管理
     *
     * @Route("/%eccube_admin_route%/adoption/examination/regist/{id}", name="admin_adoption_examination_regist", requirements={"id" = "\d+"})
     * @Template("@admin/Adoption/examination_regist.twig")
     */
    public function Examination_regist(Request $request)
    {
        $conservationId = $request->get("id");
        $conservation = $this->conservationsRepository->find($conservationId);
        if (!$conservation) {
            throw new NotFoundHttpException();
        }
        /** @var $Customer \Eccube\Entity\Customer */
        $Customer = $this->customerRepository->find($conservationId);
        if (!$Customer) {
            throw new NotFoundHttpException();
        }

        $comment = $request->get('examination_result_comment');
        $data = [
            'name' => "{$Customer->getName01()} {$Customer->getName02()}",
            'examination_comment' => "<span id='ex-comment'>{$comment}</span>"
        ];

        if ($request->isMethod('POST')) {
            $result = (int)$request->get('examination_result');
            $conservation->setExaminationStatus($result);

            $data['examination_comment'] = $comment;
            if ($result === AnilineConf::ANILINE_EXAMINATION_STATUS_CHECK_OK) {
                $conservation->setIsActive(1);
                $Customer->setIsConservation(1);
                $Customer->setRelationId($Customer->getId());
                $this->mailService->sendAdoptionExaminationMailAccept($Customer, $data);
            } elseif ($result === AnilineConf::ANILINE_EXAMINATION_STATUS_CHECK_NG) {
                $this->mailService->sendAdoptionExaminationMailReject($Customer, $data);
            }

            $entityManager = $this->entityManager;
            $entityManager->persist($Customer);
            $entityManager->flush();

            $this->addSuccess('審査結果を登録しました。', 'admin');
            return $this->redirectToRoute('admin_adoption_list');
        }

        return compact(
            'data',
            'conservation'
        );
    }
}
