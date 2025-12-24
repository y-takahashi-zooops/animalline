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

use Eccube\Entity\Customer;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Eccube\Form\Type\Front\NonMemberType;
use Eccube\Repository\Master\PrefRepository;
use Eccube\Service\CartService;
use Eccube\Service\OrderHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Eccube\Controller\NonMemberShoppingController as BaseNonMemberShoppingController;
use Symfony\Component\Form\FormFactoryInterface;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class NonMemberShoppingController extends BaseNonMemberShoppingController
{
    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * @var PrefRepository
     */
    protected $prefRepository;

    /**
     * @var OrderHelper
     */
    protected $orderHelper;

    /**
     * @var CartService
     */
    protected $cartService;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * NonMemberShoppingController constructor.
     *
     * @param ValidatorInterface $validator
     * @param PrefRepository $prefRepository
     * @param OrderHelper $orderHelper
     * @param CartService $cartService
     * @param LoggerInterface $logger
     */
    public function __construct(
        ValidatorInterface $validator,
        PrefRepository $prefRepository,
        OrderHelper $orderHelper,
        CartService $cartService,
        FormFactoryInterface $formFactory,
        LoggerInterface $logger,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->validator = $validator;
        $this->prefRepository = $prefRepository;
        $this->orderHelper = $orderHelper;
        $this->cartService = $cartService;
        $this->formFactory = $formFactory;
        $this->logger = $logger;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * 非会員処理
     *
     * @Route("/shopping/nonmember", name="shopping_nonmember")
     * @Template("Shopping/nonmember.twig")
     */
    public function index(Request $request)
    {
        // ログイン済みの場合は, 購入画面へリダイレクト.
        if ($this->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('shopping');
        }

        // カートチェック.
        $Cart = $this->cartService->getCart();
        if (!($Cart && $this->orderHelper->verifyCart($Cart))) {
            return $this->redirectToRoute('cart');
        }

        // 定期購入商品有無チェック
        if ($Cart) {
            $CartItems = $Cart->getCartItems();
            foreach ($CartItems as $CartItem) {
                if ($CartItem->getIsRepeat()) {
                    $this->addError('定期購入は会員登録が必要です');
                    return $this->redirectToRoute('shopping_error');
                }
            }
        }

        $builder = $this->formFactory->createBuilder(NonMemberType::class);

        $event = new EventArgs(
            [
                'builder' => $builder,
            ],
            $request
        );
        $this->eventDispatcher->dispatch($event, EccubeEvents::FRONT_SHOPPING_NONMEMBER_INITIALIZE);

        $form = $builder->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->logger->info('非会員お客様情報登録開始');

            $data = $form->getData();
            $Customer = new Customer();
            $Customer
                ->setName01($data['name01'])
                ->setName02($data['name02'])
                ->setKana01($data['kana01'])
                ->setKana02($data['kana02'])
                ->setCompanyName($data['company_name'])
                ->setEmail($data['email'])
                ->setPhonenumber($data['phone_number'])
                ->setPostalcode($data['postal_code'])
                ->setPref($data['pref'])
                ->setAddr01($data['addr01'])
                ->setAddr02($data['addr02']);

            // 非会員用セッションを作成
            $this->session->set(OrderHelper::SESSION_NON_MEMBER, $Customer);
            $this->session->set(OrderHelper::SESSION_NON_MEMBER_ADDRESSES, serialize([]));

            $event = new EventArgs(
                [
                    'form' => $form,
                ],
                $request
            );
            $this->eventDispatcher->dispatch($event, EccubeEvents::FRONT_SHOPPING_NONMEMBER_COMPLETE);

            if ($event->getResponse() !== null) {
                return $event->getResponse();
            }

            $this->logger->info('非会員お客様情報登録完了');

            return $this->redirectToRoute('shopping');
        }

        return [
            'form' => $form->createView(),
        ];
    }
}
