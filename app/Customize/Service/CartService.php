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

namespace Customize\Service;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\UnitOfWork;
use Eccube\Entity\Cart;
use Eccube\Entity\CartItem;
use Eccube\Entity\Customer;
use Eccube\Entity\ItemHolderInterface;
use Eccube\Entity\ProductClass;
use Eccube\Repository\CartRepository;
use Eccube\Repository\OrderRepository;
use Eccube\Repository\ProductClassRepository;
use Eccube\Service\CartService as BaseCartService;
use Eccube\Service\Cart\CartItemAllocator;
use Eccube\Service\Cart\CartItemComparator;
use Eccube\Util\StringUtil;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class CartService extends BaseCartService
{
    /**
     * @var Cart[]
     */
    protected $carts;

    /**
     * @var SessionInterface
     */
    protected $session;

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var ItemHolderInterface
     *
     * @deprecated
     */
    protected $cart;

    /**
     * @var ProductClassRepository
     */
    protected $productClassRepository;

    /**
     * @var CartRepository
     */
    protected $cartRepository;

    /**
     * @var CartItemComparator
     */
    protected $cartItemComparator;

    /**
     * @var CartItemAllocator
     */
    protected $cartItemAllocator;

    /**
     * @var OrderRepository
     */
    protected $orderRepository;

    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var AuthorizationCheckerInterface
     */
    protected $authorizationChecker;

    /**
     * CartService constructor.
     *
     * @param SessionInterface $session
     * @param EntityManagerInterface $entityManager
     * @param ProductClassRepository $productClassRepository
     * @param CartItemComparator $cartItemComparator
     * @param CartItemAllocator $cartItemAllocator
     * @param TokenStorageInterface $tokenStorage
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(
        SessionInterface $session,
        EntityManagerInterface $entityManager,
        ProductClassRepository $productClassRepository,
        CartRepository $cartRepository,
        CartItemComparator $cartItemComparator,
        CartItemAllocator $cartItemAllocator,
        OrderRepository $orderRepository,
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->session = $session;
        $this->entityManager = $entityManager;
        $this->productClassRepository = $productClassRepository;
        $this->cartRepository = $cartRepository;
        $this->cartItemComparator = $cartItemComparator;
        $this->cartItemAllocator = $cartItemAllocator;
        $this->orderRepository = $orderRepository;
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
    }
    /**
     * カートに商品を追加します.
     *
     * @param $ProductClass ProductClass 商品規格
     * @param $quantity int 数量
     * @param $isRepeat boolean 定期購入フラグ
     * @param $repeatSpan int 購入スパン
     * @param $spanUnit boolean 購入スパン単位
     * @return bool 商品を追加できた場合はtrue
     */
    public function addProduct($ProductClass, $quantity = 1, $isRepeat = null, $repeatSpan = null, $spanUnit = null)
    {
        if (!$ProductClass instanceof ProductClass) {
            $ProductClassId = $ProductClass;
            $ProductClass = $this->entityManager
                ->getRepository(ProductClass::class)
                ->find($ProductClassId);
            if (is_null($ProductClass)) {
                return false;
            }
        }

        $ClassCategory1 = $ProductClass->getClassCategory1();
        if ($ClassCategory1 && !$ClassCategory1->isVisible()) {
            return false;
        }
        $ClassCategory2 = $ProductClass->getClassCategory2();
        if ($ClassCategory2 && !$ClassCategory2->isVisible()) {
            return false;
        }

        $newItem = new CartItem();
        $newItem->setQuantity($quantity);
        $newItem->setPrice($ProductClass->getPrice02IncTax());
        $newItem->setProductClass($ProductClass);

        // 追加したカラムへデータをセット
        $newItem
            ->setIsRepeat($isRepeat)
            ->setRepeatSpan($repeatSpan)
            ->SetSpanUnit($spanUnit);

        $allCartItems = $this->mergeAllCartItems([$newItem]);
        $this->restoreCarts($allCartItems);

        return true;
    }


}
