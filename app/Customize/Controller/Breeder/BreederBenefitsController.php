<?php

namespace Customize\Controller\Breeder;

use Customize\Config\AnilineConf;
use Customize\Entity\BenefitsStatus;
use Customize\Form\Type\Front\BenefitsStatusType;
use Customize\Repository\BenefitsStatusRepository;
use DateTime;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Eccube\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class BreederBenefitsController extends AbstractController
{
    /**
     * @var BenefitsStatusRepository
     */
    protected $benefitsStatusRepository;

    /**
     * BreederBenefitsController constructor.
     *
     * @param BenefitsStatusRepository $benefitsStatusRepository
     */
    public function __construct(
        BenefitsStatusRepository $benefitsStatusRepository
    ) {
        $this->benefitsStatusRepository = $benefitsStatusRepository;
    }

    /**
     * 特典受取手続き画面.
     *
     * @Route("/breeder/member/benefits", name="breeder_benefits")
     * @Template("animalline/breeder/member/benefits.twig")
     */
    public function benefits(Request $request)
    {
        $user = $this->getUser();
        $BenefitsStatus = $this->benefitsStatusRepository->findOneBy(['register_id' => $user->getId(), 'site_type' => AnilineConf::ANILINE_SITE_TYPE_BREEDER]);
        if (!$BenefitsStatus) {
            $BenefitsStatus = new BenefitsStatus;
            $BenefitsStatus->setShippingName($user->getName01() . $user->getName02())
                ->setShippingZip($user->getPostalCode())
                ->setPref($user->getPref())
                ->setShippingCity($user->getAddr01())
                ->setShippingAddress($user->getAddr02())
                ->setShippingTel($user->getPhoneNumber());
        }

        $form = $this->createForm(BenefitsStatusType::class, $BenefitsStatus);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            switch ($request->get('mode')) {
                case 'confirm':
                    return $this->render('animalline/breeder/member/benefits_confirm.twig', [
                        'form' => $form->createView(),
                    ]);
                case 'complete':
                    $BenefitsStatus->setSiteType(AnilineConf::ANILINE_SITE_TYPE_BREEDER)
                        ->setRegisterId($user->getId())
                        ->setShippingStatus(AnilineConf::ANILINE_SHIPPING_STATUS_ACCEPT)
                        ->setShippingPref($BenefitsStatus->getPref()->getName());
                    $shippingDate = new DateTime;
                    if (intval(date("H")) >= 14) {
                        $shippingDate->modify('+1 days');
                    }
                    $BenefitsStatus->setBenefitsShippingDate($shippingDate);

                    $entityManager = $this->getDoctrine()->getManager();
                    $entityManager->persist($BenefitsStatus);
                    $entityManager->flush();

                    return $this->redirect($this->generateUrl('breeder_benefits_complete'));
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * 完了した特典.
     *
     * @Route("/breeder/member/benefits/complete", name="breeder_benefits_complete")
     * @Template("animalline/breeder/member/benefits_complete.twig")
     */
    public function benefits_complete()
    {
        return [];
    }
}
