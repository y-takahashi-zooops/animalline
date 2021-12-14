<?php

namespace Customize\Controller\Adoption;

use Customize\Config\AnilineConf;
use Customize\Entity\BenefitsStatus;
use Customize\Repository\BenefitsStatusRepository;
use Customize\Form\Type\Front\BenefitsStatusType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Eccube\Controller\AbstractController;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
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

        // FRONT_CONTACT_INDEX_INITIALIZE
        $event = new EventArgs(
            [
                'builder' => $builder,
                'benefitsStatus' => $benefitsStatus
            ],
            $request
        );
        $this->eventDispatcher->dispatch(EccubeEvents::FRONT_CONTACT_INDEX_INITIALIZE, $event);
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
                    $data = $form->getData();

                    $event = new EventArgs(
                        [
                            'form' => $form,
                            'data' => $data,
                        ],
                        $request
                    );

                    $this->eventDispatcher->dispatch(EccubeEvents::FRONT_CONTACT_INDEX_COMPLETE, $event);
                    $data = $event->getArgument('data');
                    $benefitsStatus->setSiteType(AnilineConf::ANILINE_SITE_TYPE_ADOPTION)
                                ->setRegisterId($user->getId())
                                ->setShippingStatus(AnilineConf::ANILINE_SHIPPING_STATUS_ACCEPT)
                                ->setShippingPref($benefitsStatus->getPref()->getName());
                    $shippingdate = new \DateTime();
                    $benefitsStatus->setBenefitsShippingDate($shippingdate);
                
                    $entityManager = $this->getDoctrine()->getManager();
                    $entityManager->persist($benefitsStatus);

                    $entityManager->flush();
                    return $this->redirect($this->generateUrl('benefits_complete'));
            }
            
        }

        return [
            'form' => $form->createView(),
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
