<?php

namespace Customize\Controller\Test;

use Customize\Entity\Breeders;
use Customize\Form\Type\BreedersType;
use Customize\Repository\BreedersRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/test/list_breeders")
 */
class BreedersController extends Controller
{
    /**
     * @Route("/", name="breeders_index", methods={"GET"})
     */
    public function index(BreedersRepository $breedersRepository): Response
    {
        return $this->render('test/breeders/index.twig', [
            'breeders' => $breedersRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="breeders_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $breeder = new Breeders();
        $form = $this->createForm(BreedersType::class, $breeder);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($breeder);
            $entityManager->flush();

            return $this->redirectToRoute('breeders_index');
        }

        return $this->render('test/breeders/new.twig', [
            'breeder' => $breeder,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="breeders_show", methods={"GET"})
     */
    public function show(Breeders $breeder): Response
    {
        return $this->render('test/breeders/show.twig', [
            'breeder' => $breeder,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="breeders_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Breeders $breeder): Response
    {
        $form = $this->createForm(BreedersType::class, $breeder);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('breeders_index');
        }

        return $this->render('test/breeders/edit.twig', [
            'breeder' => $breeder,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="breeders_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Breeders $breeder): Response
    {
        if ($this->isCsrfTokenValid('delete' . $breeder->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($breeder);
            $entityManager->flush();
        }

        return $this->redirectToRoute('breeders_index');
    }
}
