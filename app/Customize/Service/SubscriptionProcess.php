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
use Eccube\Repository\OrderRepository;
use Eccube\Repository\Master\OrderStatusRepository;
use Eccube\Repository\Master\OrderItemTypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Eccube\Controller\AbstractController;
use Eccube\Service\OrderHelper;
use Eccube\Service\PurchaseFlow\PurchaseFlow;
use Eccube\Service\PurchaseFlow\PurchaseContext;
use Customize\Service\SubscriptionMailService;
use Plugin\ZooopsSubscription\Entity\SubscriptionContract;
use Eccube\Repository\CustomerAddressRepository;
use Eccube\Repository\ShippingRepository;

class SubscriptionProcess extends AbstractController
{
    /**
     * @var SubscriptionContractRepository
     */
    protected $subscriptionContractRepository;

    /**
     * @var CustomerRepository
     */
    protected $customerRepository;

    /**
     * @var OrderRepository
     */
    protected $orderRepository;

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

    /**
     * @var CustomerAddressRepository
     */
    protected $customerAddressRepository;

    /**
     * @var ShippingRepository
     */
    protected $shippingRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        SubscriptionContractRepository $subscriptionContractRepository,
        CustomerRepository $customerRepository,
        OrderRepository $orderRepository,
        OrderStatusRepository $orderStatusRepository,
        OrderItemTypeRepository $orderItemTypeRepository,
        ProductRepository $productRepository,
        ProductClassRepository $productClassRepository,
        DeliveryRepository $deliveryRepository,
        OrderHelper $orderHelper,
        PurchaseFlow $shoppingPurchaseFlow,
        SubscriptionMailService $subscriptionMailService,
        CustomerAddressRepository $customerAddressRepository,
        ShippingRepository $shippingRepository
    ) {
        $this->entityManager = $entityManager;
        $this->subscriptionContractRepository = $subscriptionContractRepository;
        $this->customerRepository = $customerRepository;
        $this->orderRepository = $orderRepository;
        $this->orderStatusRepository = $orderStatusRepository;
        $this->orderItemTypeRepository = $orderItemTypeRepository;
        $this->productRepository = $productRepository;
        $this->productClassRepository = $productClassRepository;
        $this->deliveryRepository = $deliveryRepository;
        $this->orderHelper = $orderHelper;
        $this->purchaseFlow = $shoppingPurchaseFlow;
        $this->subscriptionMailService = $subscriptionMailService;
        $this->customerAddressRepository = $customerAddressRepository;
        $this->shippingRepository = $shippingRepository;
    }

    /**
     * 
     * 注文処理
     *   
     */
    public function order($Customer, $CartItems, $Order)
    {
        foreach ($CartItems as $CartItem) {
            $is_repeat = $CartItem->getIsRepeat();
            if ($is_repeat == 1) {
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
                    ->setOrder($Order)
                    ->setCustomer($Customer)
                    ->setProduct($CartItem->getProductClass()->getProduct())
                    ->setProductClass($CartItem->getProductClass())
                    ->setquantity($CartItem->getQuantity())
                    ->setRepeatSpan($repeatSpan)
                    ->setSpanUnit($spanUnit)
                    ->setContractDate(new \DateTime())
                    ->setNextDeliveryDate($nextDeliveryDate);

                $this->entityManager->persist($SubscirptionContract);
                $this->entityManager->flush();

                // $Order->setSubscriptionContract($SubscirptionContract);

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
        $SubscriptionContracts = $this->subscriptionContractRepository->findAll();

        if ($SubscriptionContracts) {
            foreach ($SubscriptionContracts as $SubscriptionContract) {
                $nextDeliveryDate = $SubscriptionContract->getNextDeliveryDate();
                if($nextDeliveryDate){
                    // 次回配送日10日前判定
                    $nextDeliveryDate = $nextDeliveryDate->format('Ymd');
                    if ($nextDeliveryDate == $target ) {
                        $Customer = $this->customerRepository->find($SubscriptionContract->getCustomer()->getId());
                        $this->subscriptionMailService->sendSubscriotionRemindMail($Customer);
                    }
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
        $SubscriptionContracts = $this->subscriptionContractRepository->findAll();

        if ($SubscriptionContracts) {
            foreach ($SubscriptionContracts as $SubscriptionContract) {
                $nextDeliveryDate = $SubscriptionContract->getNextDeliveryDate();
                if( $nextDeliveryDate ){
                    $nextDeliveryDate = $nextDeliveryDate->format('Ymd');
                    // 次回配送日7日前判定
                    if ($nextDeliveryDate == $target) {

                        $Customer = $SubscriptionContract->getCustomer();
                        $Product = $SubscriptionContract->getProduct();
                        $ProductClass = $SubscriptionContract->getProductClass();
                        // $PrevOrder = $this->orderRepository->findOneBy(['SubscriptionContract' => $SubscriptionContract]);
                        // $PrevOrder->setSubscriptionContract(NULL);
                        $PrevOrder = $SubscriptionContract->getOrder();

                        // 新規受注としてorderテーブルに新規レコード追加
                        $Order = new Order($this->orderStatusRepository->find(OrderStatus::NEW));
                        $Order
                            ->setCustomer($Customer)
                            // 前回注文情報から取得
                            ->setPayment($PrevOrder->getPayment())
                            ->setDeviceType($PrevOrder->getDeviceType())
                            ->setMessage($PrevOrder->getMessage())
                            ->setPaymentMethod($PrevOrder->getPaymentMethod())
                            ->setNote($PrevOrder->getNote())
                            // ユーザー情報
                            ->setName01($Customer->getName01())
                            ->setName02($Customer->getName02())
                            ->setKana01($Customer->getKana01())
                            ->setKana02($Customer->getKana02())
                            ->setCompanyName($Customer->getCompanyName())
                            ->setPhoneNumber($Customer->getPhoneNumber())
                            ->setPostalCode($Customer->getPostalCode())
                            ->setCountry($Customer->getCountry())
                            ->setAddr01($Customer->getAddr01())
                            ->setAddr02($Customer->getAddr02())
                            ->setPref($Customer->getPref())
                            ->setSex($Customer->getSex())
                            ->setJob($Customer->getJob())
                            ->setEmail($Customer->getEmail())
                            ->setBirth($Customer->getBirth())
                            // その他
                            ->setOrderDate(new \DateTime()) // 注文日は処理日とする
                            ->setPreOrderId($this->orderHelper->createPreOrderId());
                            // ->setSubscriptionContract($SubscriptionContract);

                        $em->persist($Order);

                        $saleType = $ProductClass->getSaleType();
                        $deliverys = $this->deliveryRepository->getDeliveries($saleType);
                        $delivery = current($deliverys);

                        // shippingテーブルに新規レコード追加
                        $Shipping = new Shipping();
                        $Shipping
                            ->setOrder($Order)
                            ->setDelivery($delivery)
                            ->setShippingDeliveryName($delivery->getName())
                            ->setShippingDate($SubscriptionContract->getNextDeliveryDate());  // 次回配送日を配送予定日にセット

                        $CustomerAddressId = $SubscriptionContract->getCustomerAddressId();
                        // CustomerAddressIDが設定されている場合は、配送情報を設定
                        if ($CustomerAddressId) {
                            $CustomerInfo = $this->customerAddressRepository->find($CustomerAddressId);
                        // CustomerAdsressが設定されていない場合は、前回の配送情報をコピーする
                        } else {
                            $CustomerInfo = $this->shippingRepository->findOneBy(['Order' => $PrevOrder]);
                        }
                        $Shipping
                            ->setName01($CustomerInfo->getName01())
                            ->setName02($CustomerInfo->getName02())
                            ->setKana01($CustomerInfo->getKana01())
                            ->setKana02($CustomerInfo->getKana02())
                            ->setCompanyName($CustomerInfo->getCompanyName())
                            ->setPhoneNumber($CustomerInfo->getPhoneNumber())
                            ->setPostalCode($CustomerInfo->getPostalCode())
                            ->setPref($CustomerInfo->getPref())
                            ->setAddr01($CustomerInfo->getAddr01())
                            ->setAddr02($CustomerInfo->getAddr02());

                        $em->persist($Shipping);

                        // 商品としてorder_itemテーブルに新規レコード追加
                        $OrderItemType = $this->orderItemTypeRepository->find(OrderItemType::PRODUCT);
                        $OrderItem = new OrderItem();

                        // order_itemテーブルにデータセット
                        $OrderItem
                            ->setOrder($Order)
                            ->setProduct($Product)
                            ->setProductClass($ProductClass)
                            ->setShipping($Shipping)
                            ->setQuantity($SubscriptionContract->getQuantity())
                            // 商品データ
                            ->setOrderItemType($OrderItemType)
                            ->setProductName($Product->getName())
                            ->setClassName1($Product->getClassName1())
                            ->setClassName2($Product->getClassName2())
                            ->setProductCode($ProductClass->getCode())
                            ->setClassCategoryName1($ProductClass->getClassCategory1())
                            ->setClassCategoryName2($ProductClass->getClassCategory2())
                            ->setPrice($ProductClass->getPrice02());

                        $em->persist($OrderItem);

                        $SubscriptionContract->setOrder($Order);
                        $em->persist($SubscriptionContract);

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

                        // 定期購入テーブルにデータセット                        
                        $repeatSpan = $SubscriptionContract->getRepeatSpan();
                        $spanUnit = $SubscriptionContract->getSpanUnit();
                        $prevDeliveryDate = $SubscriptionContract->getNextDeliveryDate();

                        $prevDeliveryDate = new \DateTime($nextDeliveryDate);
                        $nextDeliveryDate = new \DateTime($nextDeliveryDate);

                        // 次回配送日を再生成
                        if (!$spanUnit) {
                            $nextDeliveryDate = $nextDeliveryDate->modify("+ ${repeatSpan}days");
                        } else {
                            $nextDeliveryDate = $nextDeliveryDate->modify("+ ${repeatSpan}month");
                        }

                        // 次回配送日、前回配送日をセット
                        $SubscriptionContract
                            ->setPrevDeliveryDate($prevDeliveryDate)
                            ->setNextDeliveryDate($nextDeliveryDate);
                        $em->persist($SubscriptionContract);
                        $em->flush();

                        $Order->setOrderNo($Order->getId());
                        $em->persist($Order);
                        $em->flush();
                        $em->getConnection()->commit();

                        // お届け日確定メールを送信する
                        $this->subscriptionMailService->sendSubscriotionConfirmMail($Order, $Shipping, $SubscriptionContract);
                    }
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
