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

namespace Customize\Controller\Admin\Breeder;

use Customize\Form\Type\Admin\BreederExaminationInfoType;
use Customize\Repository\BreederExaminationInfoRepository;
use Customize\Repository\BreedersRepository;
use Eccube\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Customize\Config\AnilineConf;
use Customize\Entity\BreederExaminationInfo;
use Customize\Form\Type\Breeder\BreederHouseType;
use Customize\Repository\BreederHouseRepository;
use Customize\Form\Type\Admin\BreedersType;
use Eccube\Repository\CustomerRepository;
use Customize\Service\MailService;
use Doctrine\ORM\EntityManagerInterface;

class BreederExaminationController extends AbstractController
{
    /**
     * @var BreedersRepository
     */
    protected $breedersRepository;

    /**
     * @var BreederExaminationInfoRepository
     */
    protected $breederExaminationInfoRepository;

    /**
     * @var CustomerRepository
     */
    protected $customerRepository;

    /**
     * @var MailService
     */
    protected $mailService;

    /**
     * @var BreederHouseRepository
     */
    protected $breederHouseRepository;

    /**
     * BreederExaminationController constructor.
     * @param BreedersRepository $breedersRepository
     * @param BreederExaminationInfoRepository $breederExaminationInfoRepository
     * @param CustomerRepository $customerRepository
     * @param MailService $mailService
     * @param BreederHouseRepository $breederHouseRepository
     */
    public function __construct(
        BreedersRepository               $breedersRepository,
        BreederExaminationInfoRepository $breederExaminationInfoRepository,
        CustomerRepository               $customerRepository,
        MailService                      $mailService,
        BreederHouseRepository $breederHouseRepository
    ) {
        $this->breedersRepository = $breedersRepository;
        $this->breederExaminationInfoRepository = $breederExaminationInfoRepository;
        $this->customerRepository = $customerRepository;
        $this->mailService = $mailService;
        $this->breederHouseRepository = $breederHouseRepository;
    }

    /**
     * 審査情報表示ブリーダー管理
     *
     * @Route("/%eccube_admin_route%/breeder/examination/{id}", name="admin_breeder_examination", requirements={"id" = "\d+"})
     * @Template("@admin/Breeder/examination.twig")
     */
    public function Examination(Request $request): array
    {
        $breeder = $this->breedersRepository->find($request->get('id'));
        $breederExaminationInfos = $this->breederExaminationInfoRepository->findBy(['Breeder' => $breeder]);
        $breederHouseDogKind = $this->breederHouseRepository->findBy(['Breeder' => $breeder, 'pet_type' => AnilineConf::ANILINE_PET_KIND_DOG]);
        $breederHouseCatKind = $this->breederHouseRepository->findBy(['Breeder' => $breeder, 'pet_type' => AnilineConf::ANILINE_PET_KIND_CAT]);

        if (!$breederExaminationInfos or !($breederHouseDogKind or $breederHouseCatKind)) {
            throw new NotFoundHttpException();
        }
        $breederExaminationInfo = $breederExaminationInfos[0];
        $isEnablePetType = count($breederExaminationInfos) > 1;
        if ($request->get('pet_type')) {
            $breederExaminationInfo = $this->breederExaminationInfoRepository->findOneBy(['Breeder' => $breeder, 'pet_type' => $request->get('pet_type')]);
            if (!$breederExaminationInfo) {
                throw new NotFoundHttpException();
            }
        }

        $form = $this->createForm(BreederExaminationInfoType::class, $breederExaminationInfo, ['disabled' => true]);
        $form->handleRequest($request);
        if ($breederHouseDogKind) {
            $formDogKind = $this->createForm(BreederHouseType::class, $breederHouseDogKind[0], ['disabled' => true]);
        } else {
            $formDogKind = $this->createForm(BreederHouseType::class, null, ['disabled' => true]);
        }

        if ($breederHouseCatKind) {
            $formCatKind = $this->createForm(BreederHouseType::class, $breederHouseCatKind[0], ['disabled' => true]);
        } else {
            $formCatKind = $this->createForm(BreederHouseType::class, null, ['disabled' => true]);
        }
        $formBreeder = $this->createForm(BreedersType::class, $breeder, ['disabled' => true]);

        return [
            'form' => $form->createView(),
            'formBreeder' => $formBreeder->createView(),
            'petType' => $breederExaminationInfo->getPetType() == AnilineConf::ANILINE_PET_KIND_DOG ? '犬' : '猫',
            'isEnablePetType' => $isEnablePetType,
            'breederExaminationInfo' => $breederExaminationInfo,
            'formDogKind' => $formDogKind->createView(),
            'formCatKind' => $formCatKind->createView(),
            'thumbnail' => $breeder->getThumbnailPath(),
            'licenseThumbnailPath' => $breeder->getLicenseThumbnailPath(),
        ];
    }

    /**
     * 審査結果登録ブリーダー管理
     *
     * @Route("/%eccube_admin_route%/breeder/examination/regist/{id}", name="admin_breeder_examination_regist", requirements={"id" = "\d+"})
     * @Template("@admin/Breeder/examination_regist.twig")
     */
    public function Examination_regist(Request $request, BreederExaminationInfo $examination)
    {
        $breederId = $examination->getBreeder()->getId();
        /** @var $Customer \Eccube\Entity\Customer */
        $Customer = $this->customerRepository->find($breederId);
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
            $examination->setExaminationResult($result)
                ->setExaminationResultComment($comment)
                ->setInputStatus(AnilineConf::ANILINE_INPUT_STATUS_COMPLETE);

            $breeder = $this->breedersRepository->find($breederId);

            // breederの審査ステータスを変更
            if ($result == AnilineConf::ANILINE_EXAMINATION_RESULT_DECISION_OK) {
                $breeder->setExaminationStatus(AnilineConf::ANILINE_EXAMINATION_STATUS_CHECK_OK)
                    ->setIsActive(1);
            } elseif ($result == AnilineConf::ANILINE_EXAMINATION_RESULT_DECISION_NG) {
                $breeder->setExaminationStatus(AnilineConf::ANILINE_EXAMINATION_STATUS_CHECK_NG);
            }

            $data['examination_comment'] = $comment;
            if ($result === AnilineConf::ANILINE_EXAMINATION_RESULT_DECISION_OK) {
                $this->mailService->sendBreederExaminationMailAccept($Customer, $data);
                $Customer->setIsBreeder(1);
                $Customer->setRelationId($Customer->getId());
            } else {
                $this->mailService->sendBreederExaminationMailReject($Customer, $data);
            }

            $entityManager = $this->entityManager;
            $entityManager->persist($examination);
            $entityManager->persist($Customer);
            $entityManager->flush();

            $this->addSuccess('審査結果を登録しました。', 'admin');
            return $this->redirectToRoute('admin_breeder_examination', ['id' => $breederId]);
        }

        return compact(
            'examination',
            'data'
        );
    }
}
