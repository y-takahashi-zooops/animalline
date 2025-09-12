<?php

/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) EC-CUBE CO.,LTD. All Rights Reserved.
 *
 * http://www.ec-cube.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Customize\Controller\Admin\Adoption;

use Customize\Form\Type\Adoption\ConservationHouseType;
use Customize\Entity\Conservations;
use Eccube\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception as HttpException;
use Doctrine\ORM\EntityManagerInterface;

class AdoptionHouseController extends AbstractController
{


    /**
     * 犬舎・猫舎情報編集保護団体管理
     *
     * @Route("/%eccube_admin_route%/adoption/house/{id}", name="admin_adoption_house", requirements={"id" = "\d+"})
     * @Template("@admin/Adoption/house.twig")
     */
    public function House(Request $request, Conservations $conservations)
    {
        $conservationsHouse = null;
        $conservationsHouses = $conservations->getConservationsHouses();
        if (!$conservationsHouses->isEmpty()) {
            $conservationsHouse = $conservationsHouses->first();
        }
        if ($request->get('pet_type')) {
            $conservationsHouse = $conservations->getConservationHouseByPetType($request->query->getInt('pet_type'));
        }
        if (!$conservationsHouse || !$conservationsHouse->getId()) {
            throw new HttpException\NotFoundHttpException();
        }

        $form = $this->createForm(ConservationHouseType::class, $conservationsHouse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $conservationsHouse->setConservationHousePref($conservationsHouse->getPref());
            $entityManager = $this->entityManager;
            $entityManager->persist($conservationsHouse);
            $entityManager->flush();
            return $this->redirectToRoute('admin_adoption_list');
        }
        return $this->render('@admin/Adoption/house.twig', [
            'conservations' => $conservations,
            'form' => $form->createView()
        ]);
    }
}
