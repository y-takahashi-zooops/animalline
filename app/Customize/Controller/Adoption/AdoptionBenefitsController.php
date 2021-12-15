<?php

namespace Customize\Controller\Adoption;

use Customize\Config\AnilineConf;
use Customize\Entity\BenefitsStatus;
use Customize\Repository\BenefitsStatusRepository;
use Customize\Form\Type\Front\BenefitsStatusType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Eccube\Controller\AbstractController;
use Eccube\Repository\Master\PrefRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AdoptionBenefitsController extends AbstractController
{

    /**
     * @var BenefitsStatusRepository
     */
    protected $benefitsStatusRepository;

    /**
     * @var PrefRepository
     */
    protected $prefRepository;

    /**
     * BreederController constructor.
     * 
     * @param BenefitsStatusRepository $benefitsStatusRepository
     * @param PrefRepository $prefRepository
     */
    public function __construct(
        BenefitsStatusRepository    $benefitsStatusRepository,
        PrefRepository $prefRepository
    ) {
        $this->benefitsStatusRepository = $benefitsStatusRepository;
        $this->prefRepository = $prefRepository;
    }

    /**
     * 特典受取手続き画面.
     *
     * @Route("/adoption/member/benefits", name="adoption_benefits")
     * @Template("animalline/adoption/member/benefits.twig")
     */
    public function benefits(Request $request)
    {
        $dataRequest = $request->get('benefits_status');
        $Pref = $this->prefRepository->find($dataRequest['Pref'] ?? '');
        $user = $this->getUser();
        $isBenefitsStatus = $this->benefitsStatusRepository->findBy(['register_id' => $user->getId(), 'site_type' => AnilineConf::ANILINE_SITE_TYPE_ADOPTION]);
        if (!$isBenefitsStatus) {
            $benefitsStatus = new BenefitsStatus();
            $benefitsStatus->setShippingName($dataRequest['shipping_name'] ?? $user->getName01() . $user->getName02())
                        ->setShippingZip($dataRequest['shipping_zip'] ?? $user->getPostalCode())
                        ->setPref($Pref ?? $user->getPref())
                        ->setShippingCity($dataRequest['shipping_city'] ?? $user->getAddr01())
                        ->setShippingAddress($dataRequest['shipping_address'] ?? $user->getAddr02())
                        ->setShippingTel($dataRequest['shipping_tel'] ?? $user->getPhoneNumber());
        } else {
            $benefitsStatus = $isBenefitsStatus[0];
        }

        $builder = $this->formFactory->createBuilder(BenefitsStatusType::class, $benefitsStatus);
        $form = $builder->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            switch ($request->get('mode')) {
                case 'confirm':
                    $form = $builder->getForm();
                    $form->handleRequest($request);

                    return $this->render('animalline/adoption/member/benefits_confirm.twig', [
                        'form' => $form->createView(),
                    ]);

                case 'complete':
                    $benefitsStatus->setSiteType(AnilineConf::ANILINE_SITE_TYPE_ADOPTION)
                                ->setRegisterId($user->getId())
                                ->setShippingStatus(AnilineConf::ANILINE_SHIPPING_STATUS_ACCEPT)
                                ->setShippingPref($benefitsStatus->getPref()->getName());
                    $shippingdate = new \DateTime();
                    if(intval(date("H")) >= 14){
                        $shippingdate->modify('+1 days');
                    }
                    $benefitsStatus->setBenefitsShippingDate($shippingdate);
                
                    $entityManager = $this->getDoctrine()->getManager();
                    $entityManager->persist($benefitsStatus);

                    $entityManager->flush();
                    return $this->redirect($this->generateUrl('benefits_complete'));
            }
        }

        return [
            'form' => $form->createView()
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
