<?php

namespace Customize\Controller\Test;

use Customize\Entity\ConservationPets;
use Customize\Form\Type\ConservationPetsType;
use Customize\Repository\ConservationPetsRepository;
use Customize\Repository\ConservationsRepository;
use Customize\Repository\BreedsRepository;
use Customize\Repository\CoatColorsRepository;
use JsonSerializable;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;

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
     * @Route("/by_pet_kind", name="by_pet_kind", methods={"GET"}, options={"expose"=true})
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

        $breeds_formated = [];
        foreach ($breeds as $breed) {
            $breeds_formated[] = [
                'id' => $breed->getId(),
                'name' => $breed->getBreedsName()
            ];
        }
        $colors_formated = [];
        foreach ($colors as $color) {
            $colors_formated[] = [
                'id' => $color->getId(),
                'name' => $color->getCoatColorName()
            ];
        }
        $data = [
            'breeds' => $breeds_formated,
            'colors' => $colors_formated
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
}
