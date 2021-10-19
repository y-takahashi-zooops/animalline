<?php

namespace Customize\Controller\Test;

use Customize\Entity\ConservationPetImage;
use Customize\Form\Type\Adoption\ConservationPetImageType;
use Customize\Repository\ConservationPetImageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/test/list_conservation_pet_image")
 */
class ConservationPetImageController extends Controller
{
    /**
     * @Route("/", name="conservation_pet_image_index", methods={"GET"})
     */
    public function index(ConservationPetImageRepository $conservationPetImageRepository): Response
    {
        return $this->render('test/conservation_pet_image/index.twig', [
            'conservation_pet_images' => $conservationPetImageRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="conservation_pet_image_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $conservationPetImage = new ConservationPetImage();
        $form = $this->createForm(ConservationPetImageType::class, $conservationPetImage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($conservationPetImage);
            $entityManager->flush();

            return $this->redirectToRoute('conservation_pet_image_index');
        }

        return $this->render('test/conservation_pet_image/new.twig', [
            'conservation_pet_image' => $conservationPetImage,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="conservation_pet_image_show", methods={"GET"})
     */
    public function show(ConservationPetImage $conservationPetImage): Response
    {
        return $this->render('test/conservation_pet_image/show.twig', [
            'conservation_pet_image' => $conservationPetImage,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="conservation_pet_image_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, ConservationPetImage $conservationPetImage): Response
    {
        $form = $this->createForm(ConservationPetImageType::class, $conservationPetImage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('conservation_pet_image_index');
        }

        return $this->render('test/conservation_pet_image/edit.twig', [
            'conservation_pet_image' => $conservationPetImage,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="conservation_pet_image_delete", methods={"DELETE"})
     */
    public function delete(Request $request, ConservationPetImage $conservationPetImage): Response
    {
        if ($this->isCsrfTokenValid('delete'.$conservationPetImage->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($conservationPetImage);
            $entityManager->flush();
        }

        return $this->redirectToRoute('conservation_pet_image_index');
    }
}
