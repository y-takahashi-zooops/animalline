<?php

namespace Customize\Controller\Adoption;

use Customize\Config\AnilineConf;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Customize\Repository\ConservationsRepository;
use Customize\Repository\ConservationsHousesRepository;
use Eccube\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

class AdoptionExamController extends AbstractController
{
    /**
     * @var ConservationsRepository
     */
    protected $conservationsRepository;

    /**
     * @var ConservationsHousesRepository
     */
    protected $conservationsHouseRepository;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * ConservationController constructor.
     *
     * @param ConservationsRepository $conservationsRepository
     * @param ConservationsHousesRepository $conservationsHouseRepository
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        ConservationsRepository             $conservationsRepository,
        ConservationsHousesRepository       $conservationsHouseRepository
    )
    {
        $this->conservationsRepository = $conservationsRepository;
        $this->conservationsHouseRepository = $conservationsHouseRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * 保護団体登録申請画面
     *
     * @Route("/adoption/member/examination", name="adoption_examination")
     * @Template("animalline/adoption/member/examination.twig")
     */
    public function examination(Request $request)
    {
        $user = $this->getUser();
        $conservation = $this->conservationsRepository->find($user);

        $step = 1;

        $regcheck[1] = 0;
        $regcheck[2] = 0;
        $regcheck[3] = 0;

        // 基本情報が登録済みであればSTEP2を表示
        if ($conservation) {
            $step = 2;
            $regcheck[1] = 1;

            // 基本情報の取扱ペットに対応する犬舎・猫舎情報が登録されていればSTEP3を表示
            $handling_pet_kind = $conservation->getHandlingPetKind();
            $dog_house_info = $this->conservationsHouseRepository->findOneBy(["Conservation" => $conservation, "pet_type" => 1]);
            //$cat_house_info = $this->conservationsHouseRepository->findOneBy(["Conservation" => $conservation, "pet_type" => 2]);

            if($dog_house_info){$regcheck[2] = 1;}
            //if($cat_house_info){$regcheck[3] = 1;}

            // if($handling_pet_kind == 0 && $cat_house_info && $dog_house_info){$step = 3;}
            // if($handling_pet_kind == 1 && $dog_house_info){$step = 3;}
            // if($handling_pet_kind == 2 && $cat_house_info){$step = 3;}

            // 審査情報が登録されていればSTEP4を表示
            // $dog_examination_info = $this->conservationExaminationInfoRepository->findOneBy(["Conservation" => $conservation,"pet_type" => 1]);
            // $cat_examination_info = $this->conservationExaminationInfoRepository->findOneBy(["Conservation" => $conservation,"pet_type" => 2]);

            // if($handling_pet_kind == 0 && $dog_examination_info && $cat_examination_info ){$step = 4;}
            // if($handling_pet_kind == 1 && $dog_examination_info ){$step = 4;}
            // if($handling_pet_kind == 2 && $cat_examination_info ){$step = 4;}

            // 審査情報は未実装なのでSTEP3の条件を満たしていればSTEP4を表示
            if ($dog_house_info) {
                $step = 4;
            }

            // 審査申請済であればSTEP5として審査中メッセージ
            $examination_status = $conservation->getExaminationStatus();
            if ($examination_status != 0) {
                $step = 5;
            }

            // 審査結果が出ていれば審査結果を表示
        }
        return $this->render('animalline/adoption/member/examination.twig', [
            'user' => $user,
            'conservation' => $conservation,
            'step' => $step,
            'regcheck' => $regcheck,
        ]);
    }

    /**
     * 審査情報編集画面
     *
     * @Route("/adoption/member/examination_info/{pet_type}", name="adoption_examination_info", methods={"GET","POST"})
     * @Template("/animalline/adoption/member/examination_info.twig")
     */
    public function examination_info(Request $request)
    {

        // 審査情報はアンケート的な意味合いで実装する。

        // $petType = $request->get('pet_type');
        // $conservation = $this->conservationsRepository->find($this->getUser());
        // $conservationExaminationInfo = $this->conservationExaminationInfoRepository->findOneBy([
        //     'Conservation' => $conservation,
        //     'pet_type' => $petType
        // ]);
        // $isEdit = false;
        // if ($conservationExaminationInfo) {
        //     $isEdit = true;
        //     if (in_array($conservationExaminationInfo->getPedigreeOrganization(),
        //         [AnilineConf::PEDIGREE_ORGANIZATION_JKC, AnilineConf::PEDIGREE_ORGANIZATION_KC])) {
        //         $conservationExaminationInfo->setGroupOrganization($conservationExaminationInfo->getPedigreeOrganization());
        //         $conservationExaminationInfo->setPedigreeOrganization(AnilineConf::PEDIGREE_ORGANIZATION_JKC);
        //     }
        // } else {
        //     $conservationExaminationInfo = new ConservationExaminationInfo();
        // }

        // $form = $this->createForm(ConservationExaminationInfoType::class, $conservationExaminationInfo);
        // $form->handleRequest($request);

        // if ($form->isSubmitted() && $form->isValid()) {
        //     $conservationExaminationInfo->setPetType($petType)
        //         ->setConservation($conservation);
        //     $formRequest = $request->request->get('adoption_examination_info');
        //     if ($formRequest['pedigree_organization'] == AnilineConf::PEDIGREE_ORGANIZATION_JKC) {
        //         $conservationExaminationInfo->setPedigreeOrganization($formRequest['group_organization']);
        //     } else {
        //         $conservationExaminationInfo->setPedigreeOrganization($formRequest['pedigree_organization']);
        //     }

        //     if ($formRequest['pedigree_organization'] != AnilineConf::PEDIGREE_ORGANIZATION_OTHER) {
        //         $conservationExaminationInfo->setPedigreeOrganizationOther(null);
        //     }

        //     $conservationExaminationInfo->setInputStatus(AnilineConf::ANILINE_INPUT_STATUS_INPUT_COMPLETE);

        //     $entityManager = $this->getDoctrine()->getManager();
        //     $entityManager->persist($conservationExaminationInfo);
        //     $entityManager->flush();

        //     return $this->redirectToRoute('adoption_examination');
        // }

        // return $this->render('animalline/adoption/member/examination_info.twig', [
        //     'form' => $form->createView(),
        //     'isEdit' => $isEdit,
        //     'petType' => $petType == AnilineConf::ANILINE_PET_KIND_DOG ? '犬' : '猫'
        // ]);
    }

    /**
     * 審査結果提出
     *
     * @Route("/adoption/member/examination/submit", name="adoption_examination_submit")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function examination_submit(Request $request)
    {
        $entityManager = $this->entityManager;

        // 保護団体の審査ステータスを変更
        $conservation = $this->conservationsRepository->find($this->getUser());

        if($conservation->getExaminationStatus() == 0){
            $conservation->setExaminationStatus(AnilineConf::ANILINE_EXAMINATION_STATUS_NOT_CHECK);

            $entityManager->persist($conservation);

            // // 犬舎・猫舎両方のパターンがあるため配列で取得
            // $conservationExaminationInfos = $this->conservationExaminationInfoRepository->findBy([
            //     'Conservation' => $conservation,
            // ]);

            // // 審査情報のそれぞれの審査ステータスを変更
            // foreach($conservationExaminationInfos as $conservationExaminationInfo){
            //     $conservationExaminationInfo->setInputStatus(AnilineConf::ANILINE_INPUT_STATUS_SUBMIT);  
            //     $entityManager->persist($conservationExaminationInfo);
            // }

            $entityManager->flush();
        }
        
        return $this->redirectToRoute('adoption_examination');
    }
}