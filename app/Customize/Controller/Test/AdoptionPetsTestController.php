<?php

namespace Customize\Controller\Test;

use Customize\Config\AnilineConf;
use Customize\Entity\ConservationPets;
use Customize\Entity\ConservationPetImage;
use Customize\Form\Type\ConservationPetsType;
use Customize\Repository\ConservationPetsRepository;
use Customize\Repository\ConservationsRepository;
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
     * @Route("/new/{conservation_id}", name="adoption_pets_new", methods={"GET","POST"})
     */
    public function new(Request $request, ConservationsRepository $conservationsRepository): Response
    {
        $conservationPet = new ConservationPets();
        $form = $this->createForm(ConservationPetsType::class, $conservationPet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $petImage0 = (new ConservationPetImage())
                ->setImageType(AnilineConf::PET_PHOTO_TYPE_IMAGE)->setImageUri($request->get('img0'))->setSortOrder(1)->setConservationPetId($conservationPet);
            $petImage1 = (new ConservationPetImage())
                ->setImageType(AnilineConf::PET_PHOTO_TYPE_IMAGE)->setImageUri($request->get('img1'))->setSortOrder(2)->setConservationPetId($conservationPet);
            $petImage2 = (new ConservationPetImage())
                ->setImageType(AnilineConf::PET_PHOTO_TYPE_IMAGE)->setImageUri($request->get('img2'))->setSortOrder(3)->setConservationPetId($conservationPet);
            $petImage3 = (new ConservationPetImage())
                ->setImageType(AnilineConf::PET_PHOTO_TYPE_IMAGE)->setImageUri($request->get('img3'))->setSortOrder(4)->setConservationPetId($conservationPet);
            $petImage4 = (new ConservationPetImage())
                ->setImageType(AnilineConf::PET_PHOTO_TYPE_IMAGE)->setImageUri($request->get('img4'))->setSortOrder(5)->setConservationPetId($conservationPet);
            $conservationPet->addConservationPetImage($petImage0);
            $conservationPet->addConservationPetImage($petImage1);
            $conservationPet->addConservationPetImage($petImage2);
            $conservationPet->addConservationPetImage($petImage3);
            $conservationPet->addConservationPetImage($petImage4);
            $conservationPet->setThumbnailPath($request->get('img0'));

            $conservation = $conservationsRepository->find($request->get('conservation_id'));
            $conservationPet->setConservationId($conservation);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($conservationPet);
            $entityManager->persist($petImage0);
            $entityManager->persist($petImage1);
            $entityManager->persist($petImage2);
            $entityManager->persist($petImage3);
            $entityManager->persist($petImage4);
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
        if ($this->isCsrfTokenValid('delete' . $conservationPet->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($conservationPet);
            $entityManager->flush();
        }

        return $this->redirectToRoute('adoption_pets_index');
    }
}
