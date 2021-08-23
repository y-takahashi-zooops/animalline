<?php

namespace Customize\Controller\Breeder;

use Customize\Config\AnilineConf;
use Customize\Repository\BreederPetImageRepository;
use Customize\Repository\BreederPetsRepository;
use Customize\Repository\PetsFavoriteRepository;
use Eccube\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception as HttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class BreederController extends AbstractController
{
    /**
     * @var BreederPetsRepository
     */
    protected $breederPetRepository;

    /**
     * @var PetsFavoriteRepository
     */
    protected $petsFavoriteRepository;

    /**
     * @var BreederPetImageRepository
     */
    protected $breederPetImageRepository;

    /**
     * BreederController constructor.
     * @param BreederPetsRepository $breederPetRepository,
     * @param PetsFavoriteRepository $petsFavoriteRepository,
     * @param BreederPetImageRepository $breederPetImageRepository
     */
    public function __construct(
        BreederPetsRepository  $breederPetRepository,
        PetsFavoriteRepository $petsFavoriteRepository,
        BreederPetImageRepository $breederPetImageRepository
    ) {
        $this->breederPetRepository = $breederPetRepository;
        $this->petsFavoriteRepository = $petsFavoriteRepository;
        $this->breederPetImageRepository = $breederPetImageRepository;
    }


    /**
     * ペット詳細.
     *
     * @Route("/breeder/pet/detail/{id}", name="breeder_pet_detail", requirements={"id" = "\d+"})
     * @Template("animalline/breeder/pet/detail.twig")
     */
    public function petDetail(Request $request)
    {
        $isLoggedIn = (bool)$this->getUser();
        $id = $request->get('id');
        $isFavorite = false;
        $breederPet = $this->breederPetRepository->find($id);
        $favorite = $this->petsFavoriteRepository->findOneBy(['customer_id' => $this->getUser(), 'pet_id' => $id]);
        if ($favorite) {
            $isFavorite = true;
        }
        if (!$breederPet) {
            throw new HttpException\NotFoundHttpException();
        }

        $images = $this->breederPetImageRepository->findBy(
            [
                'breeder_pet_id' => $id,
                'image_type' => AnilineConf::PET_PHOTO_TYPE_IMAGE
            ]
        );
        $video = $this->breederPetImageRepository->findOneBy(
            [
                'breeder_pet_id' => $id,
                'image_type' => AnilineConf::PET_PHOTO_TYPE_VIDEO
            ]
        );

        return $this->render(
            'animalline/breeder/pet/detail.twig',
            [
                'breederPet' => $breederPet,
                'images' => $images,
                'video' => $video,
                'isFavorite' => $isFavorite,
                'isLoggedIn' => $isLoggedIn
            ]
        );
    }

    /**
     * お問い合わせ.
     *
     * @Route("/breeder/member/contact/{pet_id}", name="breeder_contact", requirements={"pet_id" = "\d+"})
     * @Template("/animalline/adoption/contact.twig")
     */
    public function contact(Request $request)
    {
        return true;
    }
}
