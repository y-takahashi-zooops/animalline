<?php

namespace Customize\Controller\Breeder;

use Customize\Config\AnilineConf;
use Customize\Repository\BreederHouseRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Customize\Entity\PetsFavorite;
use Customize\Repository\BreederPetsRepository;
use Customize\Repository\PetsFavoriteRepository;
use Eccube\Controller\AbstractController;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;

class BreederFavoriteController extends AbstractController
{
    /**
     * @var BreederPetsRepository
     */
    protected $breederPetsRepository;

    /**
     * @var PetsFavoriteRepository
     */
    protected $petsFavoriteRepository;

    /**
     * @var BreederHouseRepository
     */
    protected $breederHouseRepository;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * BreederController constructor.
     *
     * @param PetsFavoriteRepository $petsFavoriteRepository
     * @param BreederPetsRepository $breederPetsRepository
     * @param BreederHouseRepository $breederHouseRepository
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        PetsFavoriteRepository    $petsFavoriteRepository,
        BreederPetsRepository     $breederPetsRepository,
        BreederHouseRepository $breederHouseRepository
    ) {
        $this->petsFavoriteRepository = $petsFavoriteRepository;
        $this->breederPetsRepository = $breederPetsRepository;
        $this->breederHouseRepository = $breederHouseRepository;
        $this->entityManager = $entityManager;
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
        $entityManager = $this->entityManager;
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
        $favoritePetResults = $this->breederPetsRepository->findByUserFavorite($this->getUser(),1);
        $favoritePets = $paginator->paginate(
            $favoritePetResults,
            $request->query->getInt('page', 1),
            AnilineConf::ANILINE_NUMBER_ITEM_PER_PAGE
        );
        foreach ($favoritePets as $key => $favoritePet) {
            $favoritePet['pref'] = $this->breederHouseRepository->findOneBy(['Breeder' => $favoritePet[0]->getBreeder(), 'pet_type' => $favoritePet[0]->getPetKind()]);
            $favoritePets[$key] = $favoritePet;
        }

        return $this->render('animalline/breeder/favorite.twig', [
            'pets' => $favoritePets,
            'user' => $this->getUser()
        ]);
    }
}
