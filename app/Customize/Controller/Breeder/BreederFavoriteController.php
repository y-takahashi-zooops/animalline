<?php

namespace Customize\Controller\Breeder;

use Customize\Config\AnilineConf;
use Eccube\Repository\Master\PrefRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Customize\Entity\PetsFavorite;
use Customize\Repository\BreedersRepository;
use Customize\Repository\BreederPetsRepository;
use Customize\Repository\PetsFavoriteRepository;
use Eccube\Controller\AbstractController;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class BreederFavoriteController extends AbstractController
{
    /**
     * @var BreedersRepository
     */
    protected $breedersRepository;

    /**
     * @var BreederPetsRepository
     */
    protected $breederPetsRepository;

    /**
     * @var PetsFavoriteRepository
     */
    protected $petsFavoriteRepository;

    /**
     * @var PrefRepository
     */
    protected $prefRepository;

    /**
     * BreederController constructor.
     *
     * @param PetsFavoriteRepository $petsFavoriteRepository
     * @param BreedersRepository $breedersRepository
     * @param BreederPetsRepository $breederPetsRepository
     * @param PrefRepository $prefRepository
     */
    public function __construct(
        PetsFavoriteRepository    $petsFavoriteRepository,
        BreedersRepository        $breedersRepository,
        BreederPetsRepository     $breederPetsRepository,
        PrefRepository            $prefRepository
    ) {
        $this->petsFavoriteRepository = $petsFavoriteRepository;
        $this->breedersRepository = $breedersRepository;
        $this->breederPetsRepository = $breederPetsRepository;
        $this->prefRepository = $prefRepository;
    }

    /**
     * Page favorite pet
     *
     * @Route("/breeder/pet/detail/favorite_pet", name="breeder_favorite_pet")
     */
    public function favoritePet(Request $request)
    {
        $id = $request->get('id');
        $pet = $this->breederPetsRepository->find($id);
        $favorite = $this->petsFavoriteRepository->findOneBy(['Customer' => $this->getUser(), 'pet_id' => $id]);
        $breederSelf = $pet->getBreeder()->getId() == $this->getUser()->getId();
        $entityManager = $this->getDoctrine()->getManager();
        if ($breederSelf) {
            return new JsonResponse('not-allowed');
        } elseif (!$favorite) {
            $petKind = $pet->getPetKind();
            $favorite_pet = new PetsFavorite();
            $favorite_pet->setCustomer($this->getUser())
                ->setPetId($id)
                ->setSiteCategory(AnilineConf::SITE_CATEGORY_BREEDER)
                ->setPetKind($petKind);
            $entityManager->persist($favorite_pet);
            $entityManager->flush();

            $this->breederPetsRepository->incrementCount($pet);
        } else {
            $entityManager->remove($favorite);
            $entityManager->flush();

            $this->breederPetsRepository->decrementCount($pet);

            return new JsonResponse('unliked');
        }

        return new JsonResponse('liked');
    }

    /**
     * お気に入り一覧画面
     *
     * @Route("/breeder/member/favorite", name="breeder_favorite")
     * @Template("animalline/breeder/favorite.twig")
     */
    public function favorite(PaginatorInterface $paginator, Request $request): ?Response
    {
        $favoritePetResults = $this->breederPetsRepository->findByFavoriteCount();
        $favoritePets = $paginator->paginate(
            $favoritePetResults,
            $request->query->getInt('page', 1),
            AnilineConf::ANILINE_NUMBER_ITEM_PER_PAGE
        );

        return $this->render('animalline/breeder/favorite.twig', ['pets' => $favoritePets]);
    }
}
