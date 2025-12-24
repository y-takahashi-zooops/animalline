<?php

namespace Customize\Controller\Adoption;

use Customize\Config\AnilineConf;
use Customize\Entity\BenefitsStatus;
use Customize\Repository\BenefitsStatusRepository;
use Customize\Form\Type\Front\BenefitsStatusType;
use Customize\Repository\ConservationContactHeaderRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Eccube\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;

class AdoptionBenefitsController extends AbstractController
{
    /**
     * @var BenefitsStatusRepository
     */
    protected $benefitsStatusRepository;

    /**
     * @var ConservationContactHeaderRepository
     */
    protected $conservationContactHeaderRepository;

    /**
     * BreederController constructor.
     * 
     * @param BenefitsStatusRepository $benefitsStatusRepository
     * @param ConservationContactHeaderRepository $conservationContactHeaderRepository
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        BenefitsStatusRepository    $benefitsStatusRepository,
        ConservationContactHeaderRepository $conservationContactHeaderRepository,
        FormFactoryInterface $formFactory
    ) {
        $this->benefitsStatusRepository = $benefitsStatusRepository;
        $this->conservationContactHeaderRepository = $conservationContactHeaderRepository;
        
        // 親クラスのsetterメソッドを呼び出してプロパティを設定
        $this->setEntityManager($entityManager);
        $this->setFormFactory($formFactory);
    }

    /**
     * 特典受取手続き画面.
     *
     * @Route("/adoption/member/benefits", name="adoption_benefits")
     * @Template("animalline/adoption/member/benefits.twig")
     */
    public function benefits(Request $request)
    {
        $user = $this->getUser();
        $benefitsStatus = new BenefitsStatus;
        $contactHeaders = $this->conservationContactHeaderRepository->findBy(['Customer' => $user, 'contract_status' => AnilineConf::CONTRACT_STATUS_CONTRACT], ['create_date'=>'ASC']);

        $isExistedBenefits = true;
        foreach ($contactHeaders as $contactHeader) {
            $petBenefit = $this->benefitsStatusRepository->findOneBy(['site_type' => AnilineConf::SITE_CATEGORY_CONSERVATION, 'pet_id' => $contactHeader->getPet()->getId()]);
            if(!$petBenefit) {
                $isExistedBenefits = false;
                $benefitsStatus->setShippingName($user->getName01() . $user->getName02())
                    ->setPetId($contactHeader->getPet()->getId())
                    ->setShippingZip($user->getPostalCode())
                    ->setPref($Pref ?? $user->getPref())
                    ->setShippingCity($user->getAddr01())
                    ->setShippingAddress($user->getAddr02())
                    ->setShippingTel($user->getPhoneNumber());
                break;
            }
        }

        $builder = $this->formFactory->createBuilder(BenefitsStatusType::class, $benefitsStatus);
        $form = $builder->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            switch ($request->get('mode')) {
                case 'confirm':
                    return $this->render('animalline/adoption/member/benefits_confirm.twig', [
                        'form' => $form->createView()
                    ]);

                case 'complete':
                    $benefitsStatus->setSiteType(AnilineConf::ANILINE_SITE_TYPE_ADOPTION)
                                ->setRegisterId($user->getId())
                                ->setShippingStatus(AnilineConf::ANILINE_SHIPPING_STATUS_ACCEPT)
                                ->setShippingPref($benefitsStatus->getPref()->getName());
                    $shippingdate = new \DateTime();
                    if(intval(date("H")) >= 14){
                        $shippingdate->modify('+1 day');
                    }
                    $benefitsStatus->setBenefitsShippingDate($shippingdate);

                    $entityManager = $this->entityManager;
                    $entityManager->persist($benefitsStatus);

                    $entityManager->flush();
                    return $this->redirect($this->generateUrl('benefits_complete'));
            }
        }

        return [
            'form' => $form->createView(),
            'isExistedBenefits' => $isExistedBenefits
        ];
    }

    /**
     * お問い合わせ完了画面.
     *
     * @Route("/adoption/member/benefits/complete", name="benefits_complete")
     * @Template("animalline/adoption/member/benefits_complete.twig")
     */
    public function complete()
    {
        return [];
    }
}
