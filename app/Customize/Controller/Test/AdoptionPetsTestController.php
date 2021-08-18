<?php

namespace Customize\Controller\Test;

use Customize\Config\AnilineConf;
use Customize\Entity\ConservationPets;
use Customize\Form\Type\ConservationPetsType;
use Customize\Repository\ConservationPetsRepository;
use Customize\Repository\ConservationsRepository;
use Customize\Repository\BreedsRepository;
use Customize\Repository\CoatColorsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("test/list_adoption_pets")
 */
class AdoptionPetsTestController extends Controller
{
    /**
     * @Route("/", name="adoption_pets_index", methods={"GET"})
     */
    public function index(ConservationPetsRepository $conservationPetsRepository): Response
    {
        return $this->render('test/adoption_pets/index.twig', [
            'adoption_pets' => $conservationPetsRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new/{conservation_id}", name="adoption_pets_new", methods={"GET","POST"})
     */
    public function new(Request $request, ConservationsRepository $conservationsRepository): Response
    {
        $conservationPet = new ConservationPets();
        $form = $this->createForm(ConservationPetsType::class, $conservationPet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $conservation = $conservationsRepository->find($request->get('conservation_id'));
            $conservationPet->setConservationId($conservation);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($conservationPet);
            $entityManager->flush();

            return $this->redirectToRoute('adoption_pets_index');
        }

        return $this->render('test/adoption_pets/new.twig', [
            'adoption_pet' => $conservationPet,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/by_pet_kind", name="by_pet_kind", methods={"GET"})
     * @param Request $request
     * @param BreedsRepository $breedsRepository
     * @param CoatColorsRepository $coatColorsRepository
     * @return JsonResponse
     */
    public function byPetKind(Request $request, BreedsRepository $breedsRepository, CoatColorsRepository $coatColorsRepository)
    {
        $petKind = $request->get('pet_kind');
        $breeds = $breedsRepository->findBy(['pet_kind' => $petKind]);
        $colors = $coatColorsRepository->findBy(['pet_kind' => $petKind]);
        $formattedBreeds = [];
        foreach ($breeds as $breed) {
            $formattedBreeds[] = [
                'id' => $breed->getId(),
                'name' => $breed->getBreedsName()
            ];
        }
        $formattedColors = [];
        foreach ($colors as $color) {
            $formattedColors[] = [
                'id' => $color->getId(),
                'name' => $color->getCoatColorName()
            ];
        }
        $data = [
            'breeds' => $formattedBreeds,
            'colors' => $formattedColors
        ];

        return new JsonResponse($data);
    }

    /**
     * @Route("/{id}", name="adoption_pets_show", methods={"GET"})
     */
    public function show(ConservationPets $conservationPet): Response
    {
        return $this->render('test/adoption_pets/show.twig', [
            'adoption_pet' => $conservationPet,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="adoption_pets_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, ConservationPets $conservationPet): Response
    {
        $form = $this->createForm(ConservationPetsType::class, $conservationPet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('adoption_pets_index');
        }

        return $this->render('test/adoption_pets/edit.twig', [
            'adoption_pet' => $conservationPet,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="adoption_pets_delete", methods={"DELETE"})
     */
    public function delete(Request $request, ConservationPets $conservationPet): Response
    {
        if ($this->isCsrfTokenValid('delete' . $conservationPet->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($conservationPet);
            $entityManager->flush();
        }

        return $this->redirectToRoute('adoption_pets_index');
    }

    /**
     * @Route("/upload", name="test_adoption_pets_upload_crop_image", methods={"POST"}, options={"expose"=true})
     * @param Request $request
     * @return JsonResponse
     */
    public function upload(Request $request)
    {
        if (!file_exists(AnilineConf::ANILINE_IMAGE_URL_BASE . '/test/')) {
            mkdir(AnilineConf::ANILINE_IMAGE_URL_BASE . '/test/', 0777, 'R');
        }
        $folderPath = AnilineConf::ANILINE_IMAGE_URL_BASE . '/test/';
        $image_parts = explode(";base64,", $_POST['image']);
        $image_type_aux = explode("image/", $image_parts[0]);
        $image_type = $image_type_aux[1];
        $image_base64 = base64_decode($image_parts[1]);
        $file = $folderPath . uniqid() . '.' . $image_type;
        file_put_contents($file, $image_base64);
        return new JsonResponse($file);
    }
}
