<?php

namespace Customize\Controller\Adoption;

use Customize\Config\AnilineConf;
use Customize\Entity\BenefitsStatus;
use Customize\Repository\BenefitsStatusRepository;
use Customize\Form\Type\Front\BenefitsStatusType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Eccube\Controller\AbstractController;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
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
     * BreederController constructor.
     *
     * @param BenefitsStatusRepository $benefitsStatusRepository
     */
    public function __construct(
        BenefitsStatusRepository    $benefitsStatusRepository
    ) {
        $this->benefitsStatusRepository = $benefitsStatusRepository;
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
        $benefitsStatus = new BenefitsStatus();
        $builder = $this->formFactory->createBuilder(BenefitsStatusType::class, $benefitsStatus);

        $form = $builder->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $benefitsStatus->setSiteType(AnilineConf::ANILINE_SITE_TYPE_ADOPTION)
            ->setRegisterId($user->getId())
            ->setShippingStatus(AnilineConf::ANILINE_SHIPPING_STATUS_ACCEPT)
            ->setShippingPref($benefitsStatus->getShippingPref());

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($benefitsStatus);

            $entityManager->flush();
            return $this->redirect($this->generateUrl('adoption_mypage'));
        }
        return [
            'form' => $form->createView(),
        ];
    }
}
