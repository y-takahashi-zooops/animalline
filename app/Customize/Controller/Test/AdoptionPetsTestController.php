<?php

namespace Customize\Controller\Test;

use Customize\Entity\ConservationPets;
use Customize\Form\Type\ConservationPetsType;
use Customize\Repository\ConservationPetsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
     * @Route("/new", name="adoption_pets_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $conservationPet = new ConservationPets();
        $form = $this->createForm(ConservationPetsType::class, $conservationPet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
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
        if ($this->isCsrfTokenValid('delete'.$conservationPet->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($conservationPet);
            $entityManager->flush();
        }

        return $this->redirectToRoute('adoption_pets_index');
    }
}
