<?php

namespace Customize\Controller\Adoption;

use Customize\Config\AnilineConf;
use Customize\Form\Type\Front\BenefitsStatusType;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Customize\Repository\BreedersRepository;
use Eccube\Controller\AbstractController;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AdoptionBenefitsController extends AbstractController
{

    /**
     * @var BreedersRepository
     */
    protected $breedersRepository;

    /**
     * BreederController constructor.
     *
     * @param BreedersRepository $breedersRepository
     */
    public function __construct(
        BreedersRepository               $breedersRepository
    ) {
        $this->breedersRepository = $breedersRepository;
    }

    /**
     * 特典受取手続き画面.
     *
     * @Route("/adoption/member/benefits", name="adoption_benefits")
     * @Template("animalline/adoption/member/benefits.twig")
     */
    public function benefits(Request $request)
    {
        $builder = $this->formFactory->createBuilder(BenefitsStatusType::class);
        $event = new EventArgs(
            [
                'builder' => $builder,
            ],
            $request
        );
        $this->eventDispatcher->dispatch(EccubeEvents::FRONT_CONTACT_INDEX_INITIALIZE, $event);

        $form = $builder->getForm();

        return [
            'form' => $form->createView(),
        ];
    }
}
