<?php

namespace Customize\Controller\Adoption;

use Customize\Config\AnilineConf;
use Customize\Entity\DnaCheckStatusHeader;
use Customize\Form\Type\Adoption\ConservationKitDnaType;
use Customize\Service\AdoptionQueryService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Customize\Entity\DnaCheckStatus;
use Customize\Repository\ConservationPetsRepository;
use Customize\Repository\PetsFavoriteRepository;
use Eccube\Repository\Master\PrefRepository;
use Customize\Repository\ConservationContactsRepository;
use Customize\Repository\ConservationsRepository;
use Customize\Repository\ConservationsHousesRepository;
use Customize\Repository\DnaCheckStatusRepository;
use Customize\Service\DnaQueryService;
use Eccube\Repository\CustomerRepository;
use Eccube\Controller\AbstractController;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Customize\Repository\DnaCheckStatusHeaderRepository;
use DateTime;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AdoptionDnaCheck extends AbstractController
{
    /**
     * @var ConservationContactsRepository
     */
    protected $conservationContactsRepository;

    /**
     * @var AdoptionQueryService
     */
    protected $adoptionQueryService;

    /**
     * @var PetsFavoriteRepository
     */
    protected $petsFavoriteRepository;

    /**
     * @var ConservationsRepository
     */
    protected $conservationsRepository;

    /**
     * @var ConservationHouse
     */
    protected $conservationsHousesRepository;

    /**
     * @var PrefRepository
     */
    protected $prefRepository;

    /**
     * @var ConservationPetsRepository
     */
    protected $conservationPetsRepository;

    /**
     * @var CustomerRepository
     */
    protected $customerRepository;

    /**
     * @var DnaQueryService;
     */
    protected $dnaQueryService;

    /**
     * @var DnaCheckStatusRepository;
     */
    protected $dnaCheckStatusRepository;

    /**
     * @var DnaCheckStatusHeaderRepository;
     */
    protected $dnaCheckStatusHeaderRepository;

    /**
     * AdoptionController constructor.
     *
     * @param ConservationContactsRepository $conservationContactsRepository
     * @param AdoptionQueryService $conservationQueryService
     * @param PetsFavoriteRepository $petsFavoriteRepository
     * @param ConservationsRepository $conservationsRepository
     * @param PrefRepository $prefRepository
     * @param ConservationsHousesRepository $conservationsHousesRepository
     * @param ConservationPetsRepository $conservationPetsRepository
     * @param CustomerRepository $customerRepository
     * @param DnaQueryService $dnaQueryService
     * @param DnaCheckStatusRepository $dnaCheckStatusRepository
     * @param DnaCheckStatusHeaderRepository $dnaCheckStatusHeaderRepository
     */
    public function __construct(
        ConservationContactsRepository $conservationContactsRepository,
        AdoptionQueryService $adoptionQueryService,
        PetsFavoriteRepository $petsFavoriteRepository,
        ConservationsRepository $conservationsRepository,
        PrefRepository $prefRepository,
        ConservationsHousesRepository $conservationsHousesRepository,
        ConservationPetsRepository $conservationPetsRepository,
        CustomerRepository $customerRepository,
        DnaQueryService $dnaQueryService,
        DnaCheckStatusRepository $dnaCheckStatusRepository,
        DnaCheckStatusHeaderRepository $dnaCheckStatusHeaderRepository
    )
    {
        $this->conservationContactsRepository = $conservationContactsRepository;
        $this->adoptionQueryService = $adoptionQueryService;
        $this->petsFavoriteRepository = $petsFavoriteRepository;
        $this->conservationsRepository = $conservationsRepository;
        $this->prefRepository = $prefRepository;
        $this->conservationsHousesRepository = $conservationsHousesRepository;
        $this->conservationPetsRepository = $conservationPetsRepository;
        $this->customerRepository = $customerRepository;
        $this->dnaQueryService = $dnaQueryService;
        $this->dnaCheckStatusRepository = $dnaCheckStatusRepository;
        $this->dnaCheckStatusHeaderRepository = $dnaCheckStatusHeaderRepository;
    }

    /**
     *
     * 検査キット請求
     *
     * @Route("/adoption/member/dna_kit", name="adoption_examination_kit", methods={"GET","POST"})
     * @Template("animalline/adoption/member/examination_kit_list.twig")
     */
    public function adoption_examination_kit(Request $request, PaginatorInterface $paginator)
    {
        $isAll = $request->get('is_all') ?? false;
        $registerId = $this->getUser();

        $dnas = $this->dnaCheckStatusHeaderRepository->createQueryBuilder('dna')
            ->where('dna.register_id = :register_id')
            ->andWhere('dna.site_type = :site_type')
            ->setParameters([':register_id' => $registerId, ':site_type' => AnilineConf::ANILINE_SITE_TYPE_ADOPTION])
            ->select('dna.id as id, dna.kit_unit, dna.shipping_status, dna.kit_shipping_date');
        if (!$isAll) {
            $dnas->andWhere($dnas->expr()->notIn('dna.shipping_status', AnilineConf::ANILINE_SHIPPING_STATUS_SHIPPED));
        }

        $dnas->orderBy('dna.create_date', 'DESC')
            ->getQuery()
            ->getResult();

        $dnas = $paginator->paginate(
            $dnas,
            $request->query->getInt('page', 1),
            $request->query->getInt('item', AnilineConf::ANILINE_NUMBER_ITEM_PER_PAGE)
        );

        return compact('dnas');
    }

    /**
     *
     * 検査キット請求
     *
     * @Route("/adoption/member/dna_kit/new", name="adoption_examination_kit_new", methods={"GET","POST"})
     * @Template("animalline/adoption/member/examination_kit_form.twig")
     */
    public function adoption_examination_kit_new(Request $request)
    {
        $dnaCheckSatusHeader = new DnaCheckStatusHeader();
        $builder = $this->formFactory->createBuilder(ConservationKitDnaType::class, $dnaCheckSatusHeader);
        $conservation = $this->conservationsRepository->find($this->getUser()->getId());
        $conservationHouseCat = $this->conservationsHousesRepository->findOneBy(['Conservation' => $conservation, 'pet_type' => AnilineConf::ANILINE_PET_KIND_CAT]);
        $conservationHouseDog = $this->conservationsHousesRepository->findOneBy(['Conservation' => $conservation, 'pet_type' => AnilineConf::ANILINE_PET_KIND_DOG]);
        $form = $builder->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $pref = $this->prefRepository->find($request->get('conservation_kit_dna')['address']['PrefShipping']);
            $dnaCheckSatusHeader->setRegisterId($this->getUser()->getId())
                ->setSiteType(AnilineConf::ANILINE_SITE_TYPE_ADOPTION)
                ->setShippingStatus(AnilineConf::ANILINE_SHIPPING_STATUS_ACCEPT)
                ->setShippingPref($pref->getName());
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($dnaCheckSatusHeader);
            $entityManager->flush();

            $kitUnit = $dnaCheckSatusHeader->getKitUnit();
            for ($i = 0; $i < $kitUnit; $i++) {
                $Dna = (new DnaCheckStatus)
                    ->setDnaHeader($dnaCheckSatusHeader)
                    ->setPetId($dnaCheckSatusHeader->getPetId())
                    ->setSiteType(AnilineConf::ANILINE_SITE_TYPE_ADOPTION)
                    ->setKitPetRegisterDate(new DateTime);
                $entityManager->persist($Dna);
            }
            $entityManager->flush();
            return $this->redirect($this->generateUrl('adoption_examination_kit'));
        }

        return [
            'form' => $form->createView(),
            'conservation' => $conservation,
            'conservationHouseCat' => $conservationHouseCat,
            'conservationHouseDog' => $conservationHouseDog
        ];
    }

    /**
     * 検査状況確認
     *
     * @Route("/adoption/member/examination_status", name="adoption_examination_status")
     * @Template("animalline/adoption/member/examination_status.twig")
     */
    public function examination_status(Request $request, PaginatorInterface $paginator)
    {
        $dnaId = (int)$request->get('dna_id');
        if ($request->isMethod('POST') && $dnaId) {
            $dna = $this->dnaCheckStatusRepository->find($dnaId);
            if (!$dna) {
                throw new NotFoundHttpException();
            }

            $dna->setCheckStatus(AnilineConf::ANILINE_DNA_CHECK_STATUS_RESENT);
            $newDna = clone $dna;
            $newDna->setCheckStatus(AnilineConf::ANILINE_DNA_CHECK_STATUS_DEFAULT);

            $em = $this->getDoctrine()->getManager();
            $em->persist($dna);
            $em->persist($newDna);
            $em->flush();

            return $this->redirectToRoute('adoption_examination_status');
        }

        $userId = $this->getUser()->getId();
        $isAll = $request->get('is_all') ?? false;
        // TODO:dnaQueryServiceにfilterDnaAdoptionMemberを実装
        $results = $this->dnaQueryService->filterDnaBreederMember($userId, $isAll);
        $dnas = $paginator->paginate(
            $results,
            $request->query->getInt('page', 1),
            $request->query->getInt('item', AnilineConf::ANILINE_NUMBER_ITEM_PER_PAGE)
        );

        return compact('dnas');
    }
}