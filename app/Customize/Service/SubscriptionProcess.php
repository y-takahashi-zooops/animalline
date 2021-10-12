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

use Eccube\Entity\Order;
use Eccube\Entity\OrderItem;
use Eccube\Entity\Shipping;
use Eccube\Entity\Master\OrderStatus;
use Eccube\Entity\Master\OrderItemType;
use Plugin\ZooopsSubscription\Repository\SubscriptionContractRepository;
use Eccube\Repository\CustomerRepository;
use Eccube\Repository\ProductRepository;
use Eccube\Repository\ProductClassRepository;
use Eccube\Repository\DeliveryRepository;
use Eccube\Repository\Master\OrderStatusRepository;
use Eccube\Repository\Master\OrderItemTypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Eccube\Controller\AbstractController;
use Eccube\Service\OrderHelper;
use Eccube\Service\PurchaseFlow\PurchaseFlow;
use Eccube\Service\PurchaseFlow\PurchaseContext;
use Customize\Service\SubscriptionMailService;
use Eccube\Entity\CartItem;
use Plugin\ZooopsSubscription\Entity\SubscriptionContract;

class SubscriptionProcess extends AbstractController
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var SubscriptionContractRepository
     */
    protected $subscriptionContractRepository;

    /**
     * @var CustomerRepository
     */
    protected $customerRepository;

    /**
     * @var OrderStatusRepository
     */
    protected $orderStatusRepository;

    /**
     * @var OrderItemTypeRepository
     */
    protected $orderItemTypeRepository;

    /**
     * @var DeliveryRepository
     */
    protected $deliveryRepository;

    /**
     * @var OrderHelper
     */
    protected $orderHelper;

    /**
     * @var PurchaseFlow
     */
    protected $purchaseFlow;

    /**
     * @var SubscriptionMailService
     */
    protected $subscriptionMailService;

    public function __construct(
        EntityManagerInterface $entityManager,
        SubscriptionContractRepository $subscriptionContractRepository,
        CustomerRepository $customerRepository,
        OrderStatusRepository $orderStatusRepository,
        OrderItemTypeRepository $orderItemTypeRepository,
        ProductRepository $productRepository,
        ProductClassRepository $productClassRepository,
        DeliveryRepository $deliveryRepository,
        OrderHelper $orderHelper,
        PurchaseFlow $shoppingPurchaseFlow,
        SubscriptionMailService $subscriptionMailService
    ) {
        $this->entityManager = $entityManager;
        $this->subscriptionContractRepository = $subscriptionContractRepository;
        $this->customerRepository = $customerRepository;
        $this->orderStatusRepository = $orderStatusRepository;
        $this->orderItemTypeRepository = $orderItemTypeRepository;
        $this->productRepository = $productRepository;
        $this->productClassRepository = $productClassRepository;
        $this->deliveryRepository = $deliveryRepository;
        $this->orderHelper = $orderHelper;
        $this->purchaseFlow = $shoppingPurchaseFlow;
        $this->subscriptionMailService = $subscriptionMailService;
    }

    /**
     * 
     * 注文処理
     *   
     */
    public function order( $CartItems, Order $Order ){
        foreach ( $CartItems as $CartItem ){
            $is_repeat = $CartItem->getIsRepeat();
            if( $is_repeat == 1 ) {
                $repeatSpan = $CartItem->getRepeatSpan();   // 購入スパン
                $spanUnit = $CartItem->getSpanUnit();       // 購入スパン単位
                $nextDeliveryDate = new \DateTime();        // 次回配送日
                $now = new \DateTime();                     // 基準日

                // 次回配送日を作成
                if (!$spanUnit) {
                    $nextDeliveryDate = $now->modify("+ ${repeatSpan}days");
                } else {
                    $nextDeliveryDate = $now->modify("+ ${repeatSpan}months");
                }

                $SubscirptionContract = new SubscriptionContract();
                $SubscirptionContract
                    ->setCustomer($this->getUser())
                    ->setProduct($CartItem->getProductClass()->getProduct())
                    ->setProductClass($CartItem->getProductClass())
                    ->setquantity($CartItem->getQuantity())
                    ->setRepeatSpan($repeatSpan)
                    ->setSpanUnit($spanUnit)
                    ->setContractDate($now)
                    ->setNextDeliveryDate($nextDeliveryDate);

                $this->entityManager->persist($SubscirptionContract);
                $this->entityManager->flush();

                $Order->setSubscriptionContract($SubscirptionContract);

                $this->entityManager->persist($Order);
                $this->entityManager->flush();
            }
        }
    }
    
    /**
     * 
     * 次回配送日10日前処理
     *   －定期注文次回配送日変更リマインドメール送信
     */
    public function nextDeliveryDateBefore10Days(){

        $target = ((new \DateTime())->modify('+ 10days'))->format('Ymd');

        // 全件取得
        $subscriptionContracts = $this->subscriptionContractRepository->findAll();

        if ($subscriptionContracts) {
            foreach ($subscriptionContracts as $subscriptionContract) {
                $nextDeliveryDate = $subscriptionContract->getNextDeliveryDate()->format('Ymd');
                $contractDate = $subscriptionContract->getContractDate()->format('Ymd');

                // 次回配送日10日前判定
                if ($nextDeliveryDate == $target && $contractDate !== null) {
                    $Customer = $this->customerRepository->find($subscriptionContract->getCustomer()->getId());
                    $this->subscriptionMailService->sendSubscriotionRemindMail($Customer);
                }
            }
        }
    }

    /**
     * 
     * 次回配送日7日前処理
     *   －定期購入データのオーダー生成
     *   －定期注文確定メール送信
     */
    public function nextDeliveryDateBefore7Days()
    {
        $em = $this->entityManager;
        // 自動コミットをやめ、トランザクションを開始
        $em->getConnection()->setAutoCommit(false);

        $target = ((new \DateTime())->modify('+ 7days'))->format('Ymd');

        // 全件取得
        $subscriptionContracts = $this->subscriptionContractRepository->findAll();

        if ($subscriptionContracts) {
            foreach ($subscriptionContracts as $subscriptionContract) {
                $nextDeliveryDate = $subscriptionContract->getNextDeliveryDate()->format('Ymd');
                $contractDate = $subscriptionContract->getContractDate()->format('Ymd');

                // 次回配送日7日前判定
                if ($nextDeliveryDate == $target && $contractDate !== null ) {

                    $customerId = $subscriptionContract->getCustomer();
                    $productId = $subscriptionContract->getProduct();
                    $productClassId = $subscriptionContract->getProductClass();

                    $Customer = $this->customerRepository->findOneBy(['id' => $customerId]);
                    $Product = $this->productRepository->findOneby(['id' => $productId]);
                    $ProductClass = $this->productClassRepository->findOneby(['id' => $productClassId]);

                    // 新規受注としてorderテーブルに新規レコード追加
                    $OrderStatus = $this->orderStatusRepository->find(OrderStatus::NEW);
                    $Order = new Order($OrderStatus);

                    // orderテーブルにデータセット
                    $Order
                        // subscriptionContractから取得
                        ->setCustomer($Customer)
                        // TODO:Orderテーブルからレコードを取得し、値をコピーする
                        // ->setPayment($subscriptionContract->getPayment())
                        // ->setDeviceType($subscriptionContract->getDeviceType())
                        // ->setMessage($subscriptionContract->getMessage())
                        // ->setPaymentMethod($subscriptionContract->getPaymentMethod())
                        // ->setNote($subscriptionContract->getNote())
                        // ユーザー情報
                        ->setCountry($Customer->getCountry())
                        ->setPref($Customer->getPref())
                        ->setSex($Customer->getSex())
                        ->setJob($Customer->getJob())
                        ->setName01($Customer->getName01())
                        ->setName02($Customer->getName02())
                        ->setKana01($Customer->getKana01())
                        ->setKana02($Customer->getKana02())
                        ->setCompanyName($Customer->getCompanyName())
                        ->setEmail($Customer->getEmail())
                        ->setPhoneNumber($Customer->getPhoneNumber())
                        ->setPostalCode($Customer->getPostalCode())
                        ->setAddr01($Customer->getAddr01())
                        ->setAddr02($Customer->getAddr02())
                        ->setBirth($Customer->getBirth())
                        // その他
                        ->setOrderDate(new \DateTime()) // 注文日は処理日とする
                        ->setPreOrderId($this->orderHelper->createPreOrderId());

                    $em->persist($Order);
                    // flushしてidを確定させる
                    $em->flush();

                    $saleType = $ProductClass->getSaleType();
                    $deliverys = $this->deliveryRepository->getDeliveries($saleType);
                    $delivery = current($deliverys);

                    // shippingテーブルに新規レコード追加
                    $Shipping = new Shipping();
                    // shippingテーブルにデータセット
                    $Shipping
                        ->setOrder($Order)
                        ->setName01($Customer->getName01())
                        ->setName02($Customer->getName02())
                        ->setKana01($Customer->getKana01())
                        ->setKana02($Customer->getKana02())
                        ->setCompanyName($Customer->getCompanyName())
                        ->setPhoneNumber($Customer->getPhoneNumber())
                        ->setPostalCode($Customer->getPostalCode())
                        ->setPref($Customer->getPref())
                        ->setAddr01($Customer->getAddr01())
                        ->setAddr02($Customer->getAddr02())
                        ->setDelivery($delivery)
                        ->setShippingDeliveryName($delivery->getName())
                        // 次回配送日を配送予定日にセット
                        ->setShippingDate($subscriptionContract->getNextDeliveryDate());

                    $em->persist($Shipping);

                    // 商品としてorder_itemテーブルに新規レコード追加
                    $OrderItem = new OrderItem();
                    $OrderItemType = $this->orderItemTypeRepository->find(OrderItemType::PRODUCT);

                    // order_itemテーブルにデータセット
                    $OrderItem
                        ->setOrder($Order)
                        ->setProduct($Product)
                        ->setProductClass($ProductClass)
                        ->setShipping($Shipping)
                        ->setQuantity($subscriptionContract->getQuantity())
                        ->setOrderItemType($OrderItemType)
                        // 商品データ
                        ->setProductName($Product->getName())
                        ->setClassName1($Product->getClassName1())
                        ->setClassName2($Product->getClassName2())
                        ->setProductCode($ProductClass->getCode())
                        ->setClassCategoryName1($ProductClass->getClassCategory1())
                        ->setClassCategoryName2($ProductClass->getClassCategory2())
                        ->setPrice($ProductClass->getPrice02());

                    $em->persist($OrderItem);

                    // 各要素を追加
                    $Shipping->addOrderItem($OrderItem);
                    $Order->addOrderItem($OrderItem);
                    $OrderItem->setOrder($Order);
                    $OrderItem->setShipping($Shipping);
                    $Order->addShipping($Shipping);

                    // 明細の正規化
                    $purchaceContext = new PurchaseContext($Order, $Customer);
                    $flowResult = $this->purchaseFlow->validate($Order, $purchaceContext);

                    // エラー処理
                    if ($flowResult->hasWarning()) {
                        foreach ($flowResult->getWarning() as $warning) {
                            $this->addWarning($warning->getMessage(), 'admin');
                        }
                    }

                    if ($flowResult->hasError()) {
                        foreach ($flowResult->getErrors() as $error) {
                            $this->addError($error->getMessage(), 'admin');
                        }
                    }

                    // PurchaseFlowにて更新した内容をDBに反映
                    $em->persist($Order);
                    $em->persist($OrderItem);
                    $em->persist($Shipping);

                    // subscriptionContractテーブルにデータセット                        
                    $repeatSpan = $subscriptionContract->getRepeatSpan();
                    $spanUnit = $subscriptionContract->getSpanUnit();
                    $prevDeliveryDate = $subscriptionContract->getNextDeliveryDate();

                    $prevDeliveryDate = new \DateTime($nextDeliveryDate);
                    $nextDeliveryDate = new \DateTime($nextDeliveryDate);

                    // 次回配送日を再生成
                    if (!$spanUnit) {
                        $nextDeliveryDate = $nextDeliveryDate->modify("+ ${repeatSpan}days");
                    } else {
                        $nextDeliveryDate = $nextDeliveryDate->modify("+ ${repeatSpan}month");
                    }

                    // 次回配送日、前回配送日をセット
                    $subscriptionContract
                        ->setPrevDeliveryDate($prevDeliveryDate)
                        ->setNextDeliveryDate($nextDeliveryDate);
                    $em->persist($subscriptionContract);

                    // お届け日確定メールを送信する
                    $this->subscriptionMailService->sendSubscriotionConfirmMail($Order, $Shipping, $subscriptionContract);
                }
            }
        }
    }

    /**
     * 定期購入情報編集
     * 
     * @param $repeat_span 配送スパン
     * @param $span_unit 配送スパン単位
     * @param $next_delivery_date 次回配送日
     * @param $SubscriptionContract 定期購入情報
     * 
     */
    public function ChangeSubscription($repeatSpan, $spanUnit, $nextDeliveryDate, $SubscriptionContract)
    {
        // お届けサイクルの変更
        if ($repeatSpan) {
            $SubscriptionContract
                ->setRepeatSpan($repeatSpan)
                ->setSpanUnit($spanUnit);
        }
        // 次回お届け日の変更
        else {
            $SubscriptionContract->setNextDeliveryDate($nextDeliveryDate);
        }

        $this->entityManager->persist($SubscriptionContract);
        $this->entityManager->flush();
    }

    /**
     * 定期購入停止
     * 
     * @param $SubscriptionContract 定期購入情報
     * 
     */
    public function StopSubscription($SubscriptionContract)
    {
        $SubscriptionContract->setNextDeliveryDate(null);
        $this->entityManager->persist($SubscriptionContract);
        $this->entityManager->flush();
    }
}
