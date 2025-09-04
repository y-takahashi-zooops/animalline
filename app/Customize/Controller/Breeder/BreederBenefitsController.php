<?php

namespace Customize\Controller\Breeder;

use Customize\Config\AnilineConf;
use Customize\Entity\BenefitsStatus;
use Customize\Form\Type\Front\BenefitsStatusType;
use Customize\Repository\BenefitsStatusRepository;
use Customize\Repository\BreederContactHeaderRepository;
use DateTime;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Eccube\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

class BreederBenefitsController extends AbstractController
{
    /**
     * @var BenefitsStatusRepository
     */
    protected $benefitsStatusRepository;

    /**
     * @var BreederContactHeaderRepository
     */
    protected $breederContactHeaderRepository;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * BreederBenefitsController constructor.
     *
     * @param BenefitsStatusRepository $benefitsStatusRepository
     * @param BreederContactHeaderRepository $breederContactHeaderRepository
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        BenefitsStatusRepository $benefitsStatusRepository,
        BreederContactHeaderRepository $breederContactHeaderRepository
    ) {
        $this->benefitsStatusRepository = $benefitsStatusRepository;
        $this->breederContactHeaderRepository = $breederContactHeaderRepository;
        $this->entityManager = $entityManager;
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
        $benefitsStatus = new BenefitsStatus;
        $contactHeaders = $this->breederContactHeaderRepository->findBy(['Customer' => $user, 'contract_status' => AnilineConf::CONTRACT_STATUS_CONTRACT], ['create_date'=>'ASC']);

        $isExistedBenefits = true;
        foreach ($contactHeaders as $contactHeader) {
            $petBenefit = $this->benefitsStatusRepository->findOneBy(['site_type' => AnilineConf::SITE_CATEGORY_BREEDER, 'pet_id' => $contactHeader->getPet()->getId()]);
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

        $form = $this->createForm(BenefitsStatusType::class, $benefitsStatus);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            switch ($request->get('mode')) {
                case 'confirm':
                    return $this->render('animalline/breeder/member/benefits_confirm.twig', [
                        'form' => $form->createView()
                    ]);
                case 'complete':
                    $benefitsStatus->setSiteType(AnilineConf::ANILINE_SITE_TYPE_BREEDER)
                        ->setRegisterId($user->getId())
                        ->setShippingStatus(AnilineConf::ANILINE_SHIPPING_STATUS_ACCEPT)
                        ->setShippingPref($benefitsStatus->getPref()->getName());
                    $shippingDate = new DateTime;
                    if (intval(date("H")) >= 14) {
                        $shippingDate->modify('+1 day');
                    }
                    $benefitsStatus->setBenefitsShippingDate($shippingDate);

                    $entityManager = $this->entityManager;
                    $entityManager->persist($benefitsStatus);
                    $entityManager->flush();

                    return $this->redirect($this->generateUrl('breeder_benefits_complete'));
            }
        }

        return [
            'form' => $form->createView(),
            'isExistedBenefits' => $isExistedBenefits
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
