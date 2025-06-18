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

namespace Eccube\Controller\Admin\Setting\Shop;

use Eccube\Controller\AbstractController;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Eccube\Form\Type\Admin\ShopMasterType;
use Eccube\Repository\BaseInfoRepository;
use Eccube\Util\CacheUtil;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
// use Twig_Environment;
use Twig\Environment;
use Symfony\Component\Form\FormFactoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Class ShopController
 */
class ShopController extends AbstractController
{
    /**
     * @var Environment
     */
    protected $twig;

    /**
     * @var BaseInfoRepository
     */
    protected $baseInfoRepository;

    /**
     * @var FormFactoryInterface
     */
    protected FormFactoryInterface $formFactory;

    /**
     * @var EntityManagerInterface
     */
    protected EntityManagerInterface $entityManager;

    /**
     * ShopController constructor.
     *
     * @param Environment $twig
     * @param BaseInfoRepository $baseInfoRepository
     * @param FormFactoryInterface $formFactory
     */
    public function __construct(
        Environment $twig,
        BaseInfoRepository $baseInfoRepository,
        FormFactoryInterface $formFactory,
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $eventDispatcher,
        SessionInterface $session
    )
    {
        $this->baseInfoRepository = $baseInfoRepository;
        $this->twig = $twig;
        $this->formFactory = $formFactory;
        $this->entityManager = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->session = $session;
    }

    /**
     * @Route("/%eccube_admin_route%/setting/shop", name="admin_setting_shop")
     * @Template("@admin/Setting/Shop/shop_master.twig")
     *
     * @param Request $request
     *
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function index(Request $request, CacheUtil $cacheUtil)
    {
        $BaseInfo = $this->baseInfoRepository->get();
        $builder = $this->formFactory
            ->createBuilder(ShopMasterType::class, $BaseInfo);

        $CloneInfo = clone $BaseInfo;
        $this->entityManager->detach($CloneInfo);

        $event = new EventArgs(
            [
                'builder' => $builder,
                'BaseInfo' => $BaseInfo,
            ],
            $request
        );
        $this->eventDispatcher->dispatch($event, EccubeEvents::ADMIN_SETTING_SHOP_SHOP_INDEX_INITIALIZE);

        $form = $builder->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($BaseInfo);
            $this->entityManager->flush();

            $event = new EventArgs(
                [
                    'form' => $form,
                    'BaseInfo' => $BaseInfo,
                ],
                $request
            );
            $this->eventDispatcher->dispatch(
                $event,
                EccubeEvents::ADMIN_SETTING_SHOP_SHOP_INDEX_COMPLETE
            );

            // キャッシュの削除
            $cacheUtil->clearDoctrineCache();

            $this->addSuccess('admin.common.save_complete', 'admin');

            return $this->redirectToRoute('admin_setting_shop');
        }

        $this->twig->addGlobal('BaseInfo', $CloneInfo);

        return [
            'form' => $form->createView(),
        ];
    }
}
