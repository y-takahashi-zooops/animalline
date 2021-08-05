<?php

namespace Customize\Controller\Test;

use Customize\Entity\CoatColors;
use Customize\Form\Type\CoatColorsType;
use Customize\Repository\CoatColorsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/test/list_coat_colors")
 */
class CoatColorsController extends Controller
{
    /**
     * @Route("/", name="coat_colors_index", methods={"GET"})
     */
    public function index(CoatColorsRepository $coatColorsRepository): Response
    {
        return $this->render('test/coat_colors/index.html.twig', [
            'coat_colors' => $coatColorsRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="coat_colors_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $coatColor = new CoatColors();
        $form = $this->createForm(CoatColorsType::class, $coatColor);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($coatColor);
            $entityManager->flush();

            return $this->redirectToRoute('coat_colors_index');
        }

        return $this->render('test/coat_colors/new.html.twig', [
            'coat_color' => $coatColor,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="coat_colors_show", methods={"GET"})
     */
    public function show(CoatColors $coatColor): Response
    {
        return $this->render('test/coat_colors/show.html.twig', [
            'coat_color' => $coatColor,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="coat_colors_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, CoatColors $coatColor): Response
    {
        $form = $this->createForm(CoatColorsType::class, $coatColor);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('coat_colors_index');
        }

        return $this->render('test/coat_colors/edit.html.twig', [
            'coat_color' => $coatColor,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="coat_colors_delete", methods={"DELETE"})
     */
    public function delete(Request $request, CoatColors $coatColor): Response
    {
        if ($this->isCsrfTokenValid('delete' . $coatColor->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($coatColor);
            $entityManager->flush();
        }

        return $this->redirectToRoute('coat_colors_index');
    }
}
