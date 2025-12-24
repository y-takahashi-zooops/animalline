<?php

namespace Customize\Controller\Adoption;

use Customize\Config\AnilineConf;
use Customize\Repository\ConservationsHousesRepository;
use Eccube\Repository\CustomerRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Customize\Entity\PetsFavorite;
use Customize\Repository\ConservationPetsRepository;
use Customize\Repository\PetsFavoriteRepository;
use Eccube\Controller\AbstractController;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;

class AdoptionFavoritePetController extends AbstractController
{
    /**
     * @var ConservationPetsRepository
     */
    protected $conservationPetsRepository;

    /**
     * @var PetsFavoriteRepository
     */
    protected $petsFavoriteRepository;

    /**
     * @var ConservationsHousesRepository
     */
    protected $conservationsHousesRepository;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * AdoptionController constructor.
     *
     * @param ConservationPetsRepository $conservationPetsRepository
     * @param PetsFavoriteRepository $petsFavoriteRepository
     * @param ConservationsHousesRepository $conservationsHousesRepository
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        ConservationPetsRepository    $conservationPetsRepository,
        PetsFavoriteRepository        $petsFavoriteRepository,
        ConservationsHousesRepository $conservationsHousesRepository
    ) {
        $this->conservationPetsRepository = $conservationPetsRepository;
        $this->petsFavoriteRepository = $petsFavoriteRepository;
        $this->conservationsHousesRepository = $conservationsHousesRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * favorite pet
     *
     * @Route("/adoption/pet/detail/favorite_pet", name="adoption_favorite_pet")
     * @param Request $request
     * @return JsonResponse
     */
    public function favoritePet(Request $request)
    {
        $id = $request->get('id');
        $pet = $this->conservationPetsRepository->find($id);
        $favorite = $this->petsFavoriteRepository->findOneBy(['Customer' => $this->getUser(), 'pet_id' => $id]);
        $adoptionSelf = $pet->getConservation()->getId() == $this->getUser()->getId();
        $entityManager = $this->entityManager;
        if ($adoptionSelf) {
            return new JsonResponse('not-allowed');
        } elseif (!$favorite) {
            $petKind = $pet->getPetKind();
            $favorite_pet = new PetsFavorite();
            $favorite_pet->setCustomer($this->getUser())
                ->setPetId($id)
                ->setSiteCategory(AnilineConf::SITE_CATEGORY_CONSERVATION)
                ->setPetKind($petKind);
            $entityManager->persist($favorite_pet);
            $entityManager->flush();

            $this->conservationPetsRepository->incrementCount($pet);
        } else {
            $entityManager->remove($favorite);
            $entityManager->flush();

            $this->conservationPetsRepository->decrementCount($pet);

            return new JsonResponse('unliked');
        }

        return new JsonResponse('liked');
    }

    /**
     * お気に入り一覧画面
     *
     * @Route("/adoption/member/favorite", name="adoption_favorite")
     * @Template("animalline/adoption/favorite.twig")
     */
    public function favorite(PaginatorInterface $paginator, Request $request): ?Response
    {
        $favoritePetResults = $this->conservationPetsRepository->findByFavoriteCount();
        $favoritePets = $paginator->paginate(
            $favoritePetResults,
            $request->query->getInt('page', 1),
            AnilineConf::ANILINE_NUMBER_ITEM_PER_PAGE
        );

        foreach ($favoritePets as $key => $favoritePet) {
            $favoritePet['pref'] = $this->conservationsHousesRepository->findOneBy(['Conservation' => $favoritePet[0]->getConservation(), 'pet_type' => $favoritePet[0]->getPetKind()]);
            $favoritePets[$key] = $favoritePet;
        }

        return $this->render('animalline/adoption/favorite.twig', [
            'pets' => $favoritePets,
            'user' => $this->getUser()
        ]);
    }
}
