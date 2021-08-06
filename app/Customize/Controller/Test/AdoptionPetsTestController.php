<?php

namespace Customize\Controller\Test;

use Customize\Entity\AdoptionPets;
use Customize\Form\Type\AdoptionPetsType;
use Customize\Repository\AdoptionPetsRepository;
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
    public function index(AdoptionPetsRepository $adoptionPetsRepository): Response
    {
        return $this->render('test/adoption_pets/index.twig', [
            'adoption_pets' => $adoptionPetsRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="adoption_pets_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $adoptionPet = new AdoptionPets();
        $form = $this->createForm(AdoptionPetsType::class, $adoptionPet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($adoptionPet);
            $entityManager->flush();

            return $this->redirectToRoute('adoption_pets_index');
        }

        return $this->render('test/adoption_pets/new.twig', [
            'adoption_pet' => $adoptionPet,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="adoption_pets_show", methods={"GET"})
     */
    public function show(AdoptionPets $adoptionPet): Response
    {
        return $this->render('test/adoption_pets/show.twig', [
            'adoption_pet' => $adoptionPet,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="adoption_pets_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, AdoptionPets $adoptionPet): Response
    {
        $form = $this->createForm(AdoptionPetsType::class, $adoptionPet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('adoption_pets_index');
        }

        return $this->render('test/adoption_pets/edit.twig', [
            'adoption_pet' => $adoptionPet,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="adoption_pets_delete", methods={"DELETE"})
     */
    public function delete(Request $request, AdoptionPets $adoptionPet): Response
    {
        if ($this->isCsrfTokenValid('delete'.$adoptionPet->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($adoptionPet);
            $entityManager->flush();
        }

        return $this->redirectToRoute('adoption_pets_index');
    }
}
