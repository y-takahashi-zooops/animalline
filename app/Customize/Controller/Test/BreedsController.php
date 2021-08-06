<?php

namespace Customize\Controller\Test;

use Customize\Entity\Breeds;
use Customize\Form\Type\BreedsType;
use Customize\Repository\BreedsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/test/list_breeds")
 */
class BreedsController extends Controller
{
    /**
     * @Route("/", name="breeds_index", methods={"GET"})
     */
    public function index(BreedsRepository $breedsRepository): Response
    {
        return $this->render('test/breeds/index.twig', [
            'breeds' => $breedsRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="breeds_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $breed = new Breeds();
        $form = $this->createForm(BreedsType::class, $breed);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($breed);
            $entityManager->flush();

            return $this->redirectToRoute('breeds_index');
        }

        return $this->render('test/breeds/new.twig', [
            'breed' => $breed,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="breeds_show", methods={"GET"})
     */
    public function show(Breeds $breed): Response
    {
        return $this->render('test/breeds/show.twig', [
            'breed' => $breed,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="breeds_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Breeds $breed): Response
    {
        $form = $this->createForm(BreedsType::class, $breed);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('breeds_index');
        }

        return $this->render('test/breeds/edit.twig', [
            'breed' => $breed,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="breeds_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Breeds $breed): Response
    {
        if ($this->isCsrfTokenValid('delete' . $breed->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($breed);
            $entityManager->flush();
        }

        return $this->redirectToRoute('breeds_index');
    }
}
