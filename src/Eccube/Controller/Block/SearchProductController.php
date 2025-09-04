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

namespace Eccube\Controller\Block;

use Eccube\Controller\AbstractController;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Eccube\Form\Type\SearchProductBlockType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class SearchProductController extends AbstractController
{
    /**
     * @var RequestStack
     */
    protected RequestStack $requestStack;
    
    /**
     * @var FormFactoryInterface
     */
    protected FormFactoryInterface $formFactory;

    public function __construct(
        RequestStack $requestStack,
        FormFactoryInterface $formFactory,
        EventDispatcherInterface $eventDispatcher,
    ) {
        $this->requestStack = $requestStack;
        $this->formFactory = $formFactory;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @Route("/block/search_product", name="block_search_product", methods={"GET"})
     * @Route("/block/search_product_sp", name="block_search_product_sp", methods={"GET"})
     * 
     * @Template("Block/search_product.twig")
     */
    public function index(Request $request)
    {
        $builder = $this->formFactory
            ->createNamedBuilder('', SearchProductBlockType::class)
            ->setMethod('GET');

        $event = new EventArgs(
            [
                'builder' => $builder,
            ],
            $request
        );

        $this->eventDispatcher->dispatch($event, EccubeEvents::FRONT_BLOCK_SEARCH_PRODUCT_INDEX_INITIALIZE);

        $request = $this->requestStack->getMainRequest();

        $form = $builder->getForm();
        $form->handleRequest($request);

        return [
            'form' => $form->createView(),
        ];
    }
}
