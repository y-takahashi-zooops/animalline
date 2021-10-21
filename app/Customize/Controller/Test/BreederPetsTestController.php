<?php

namespace Customize\Controller\Test;

use Customize\Entity\BreederPets;
use Customize\Form\Type\Breeder\BreederPetsType;
use Customize\Repository\BreederPetsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("test/list_breeder_pets")
 */
class BreederPetsTestController extends Controller
{
    /**
     * @Route("/", name="breeder_pets_index", methods={"GET"})
     */
    public function index(BreederPetsRepository $breederPetsRepository): Response
    {
        return $this->render('test/breeder_pets/index.twig', [
            'breeder_pets' => $breederPetsRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="breeder_pets_new_test", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $breederPet = new BreederPets();
        $form = $this->createForm(BreederPetsType::class, $breederPet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($breederPet);
            $entityManager->flush();

            return $this->redirectToRoute('breeder_pets_index');
        }

        return $this->render('test/breeder_pets/new.twig', [
            'breeder_pet' => $breederPet,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="breeder_pets_show", methods={"GET"})
     */
    public function show(BreederPets $breederPet): Response
    {
        return $this->render('test/breeder_pets/show.twig', [
            'breeder_pet' => $breederPet,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="breeder_pets_edit_test", methods={"GET","POST"})
     */
    public function edit(Request $request, BreederPets $breederPet): Response
    {
        $form = $this->createForm(BreederPetsType::class, $breederPet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('breeder_pets_index');
        }

        return $this->render('test/breeder_pets/edit.twig', [
            'breeder_pet' => $breederPet,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="breeder_pets_delete", methods={"DELETE"})
     */
    public function delete(Request $request, BreederPets $breederPet): Response
    {
        if ($this->isCsrfTokenValid('delete'.$breederPet->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($breederPet);
            $entityManager->flush();
        }

        return $this->redirectToRoute('breeder_pets_index');
    }
}
