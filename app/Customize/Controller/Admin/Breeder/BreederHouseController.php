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

namespace Customize\Controller\Admin\Breeder;

use Customize\Entity\Breeders;
use Eccube\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Customize\Form\Type\Breeder\BreederHouseType;
use Customize\Repository\BreederHouseRepository;
use Doctrine\ORM\EntityManagerInterface;

class BreederHouseController extends AbstractController
{
    /**
     * @var BreederHouseRepository
     */
    protected $breederHouseRepository;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    public function __construct(
        BreederHouseRepository $breederHouseRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->breederHouseRepository = $breederHouseRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * 犬舎・猫舎情報編集ブリーダー管理
     *
     * @Route("/%eccube_admin_route%/breeder/house/{id}", name="admin_breeder_house", requirements={"id" = "\d+"})
     * @Template("@admin/Breeder/house.twig")
     */
    public function House(Request $request, Breeders $breeder)
    {
        $houses = $this->breederHouseRepository->findBy(['Breeder' => $breeder]);
        if (!$houses) {
            throw new NotFoundHttpException();
        }
        $house = $houses[0]; // show first house by default.
        $isEnablePetType = count($houses) > 1; // only allow select pet type if breeder have both.

        $petType = $request->get('pet_type'); // from GET request to show house by pet type.
        if ($petType) {
            $house = $this->breederHouseRepository->findOneBy(['Breeder' => $breeder, 'pet_type' => $petType]);
            if (!$house) {
                throw new NotFoundHttpException();
            }
        }

        $form = $this->createForm(BreederHouseType::class, $house);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $house->setBreederHousePref($house->getBreederHousePrefId()['name'] ?? '');

            $entityManager = $this->entityManager;
            $entityManager->persist($house);
            $entityManager->flush();

            return $this->redirectToRoute('admin_breeder_list');
        }

        return [
            'form' => $form->createView(),
            'house' => $house,
            'isEnablePetType' => $isEnablePetType
        ];
    }
}
