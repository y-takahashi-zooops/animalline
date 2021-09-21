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

namespace Customize\Controller;

use Eccube\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Eccube\Form\Type\Front\ContactType;

class CustomTopController extends AbstractController
{
    /**
     * @Route("/ec", name="homepage")
     * @Template("index.twig")
     */
    public function index()
    {
        return [];
    }

    /**
     * @Route("/", name="ani_homepage")
     * @Template("ani_index.twig")
     */
    public function animalline_index()
    {
        return [];
    }

    /**
     * @Route("/terms", name="ani_terms")
     * @Template("ani_terms.twig")
     */
    public function terms()
    {
        return [];
    }

    /**
     * @Route("/company", name="ani_company")
     * @Template("ani_company.twig")
     */
    public function company()
    {
        return [];
    }

    /**
     * @Route("/contact", name="ani_contact")
     * @Template("ani_contact.twig")
     */
    public function contact(Request $request)
    {
        $builder = $this->formFactory->createBuilder(ContactType::class);

        if ($this->isGranted('ROLE_ADOPTION_USER')) {
            /** @var Customer $user */
            $user = $this->getUser();
            $builder->setData(
                [
                    'name01' => $user->getName01(),
                    'name02' => $user->getName02(),
                    'kana01' => $user->getKana01(),
                    'kana02' => $user->getKana02(),
                    'postal_code' => $user->getPostalCode(),
                    'pref' => $user->getPref(),
                    'addr01' => $user->getAddr01(),
                    'addr02' => $user->getAddr02(),
                    'phone_number' => $user->getPhoneNumber(),
                    'email' => $user->getEmail(),
                ]
            );
        }

        // FRONT_CONTACT_INDEX_INITIALIZE
        $event = new EventArgs(
            [
                'builder' => $builder,
            ],
            $request
        );
        $this->eventDispatcher->dispatch(EccubeEvents::FRONT_CONTACT_INDEX_INITIALIZE, $event);

        $form = $builder->getForm();
        $form->handleRequest($request);      

        return [
            'form' => $form->createView(),
        ];
    }
}
