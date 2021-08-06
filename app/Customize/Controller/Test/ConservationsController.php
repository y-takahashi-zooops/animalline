<?php

namespace Customize\Controller\Test;

use Customize\Entity\Conservations;
use Customize\Form\Type\ConservationsType;
use Customize\Repository\ConservationsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/test/list_adoptions")
 */
class ConservationsController extends Controller
{
    /**
     * @Route("/", name="conservations_index", methods={"GET"})
     */
    public function index(ConservationsRepository $conservationsRepository): Response
    {
        return $this->render('test/conservations/index.html.twig', [
            'conservations' => $conservationsRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="conservations_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $conservation = new Conservations();
        $form = $this->createForm(ConservationsType::class, $conservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($conservation);
            $entityManager->flush();

            return $this->redirectToRoute('conservations_index');
        }

        return $this->render('test/conservations/new.html.twig', [
            'conservation' => $conservation,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="conservations_show", methods={"GET"})
     */
    public function show(Conservations $conservation): Response
    {
        return $this->render('test/conservations/show.html.twig', [
            'conservation' => $conservation,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="conservations_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Conservations $conservation): Response
    {
        $form = $this->createForm(ConservationsType::class, $conservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('conservations_index');
        }

        return $this->render('test/conservations/edit.html.twig', [
            'conservation' => $conservation,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="conservations_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Conservations $conservation): Response
    {
        if ($this->isCsrfTokenValid('delete'.$conservation->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($conservation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('conservations_index');
    }
}
