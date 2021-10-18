<?php

namespace Plugin\ZooopsSubscription\Entity;

use Doctrine\ORM\Mapping as ORM;

if (!class_exists('\Plugin\ZooopsSubscription\Entity\SubscriptionContract')) {
    /**
     * SubscriptionContract
     *
     * @ORM\Table(name="plg_zooops_subscription_contract")
     * @ORM\InheritanceType("SINGLE_TABLE")
     * @ORM\DiscriminatorColumn(name="discriminator_type", type="string", length=255)
     * @ORM\HasLifecycleCallbacks()
     * @ORM\Entity(repositoryClass="Plugin\ZooopsSubscription\Repository\SubscriptionContractRepository")
     */
    class SubscriptionContract extends \Eccube\Entity\AbstractEntity
    {
        /**
         * @var int
         *
         * @ORM\Column(name="id", type="integer", options={"unsigned":true})
         * @ORM\Id
         * @ORM\GeneratedValue(strategy="IDENTITY")
         */
        private $id;

        /**
         * @var \Eccube\Entity\Order
         *
         * @ORM\ManyToOne(targetEntity="Eccube\Entity\Order", inversedBy="SubscriptionContracts")
         * @ORM\JoinColumns({
         *   @ORM\JoinColumn(name="order_id", referencedColumnName="id")
         * })
         */
        private $Order;

        /**
         * @var \Eccube\Entity\Customer
         *
         * @ORM\ManyToOne(targetEntity="Eccube\Entity\Customer", inversedBy="SubscriptionContracts")
         * @ORM\JoinColumns({
         *   @ORM\JoinColumn(name="customer_id", referencedColumnName="id")
         * })
         */
        private $Customer;

        /**
         * @var int
         *
         * @ORM\Column(name="customer_address_id", type="integer", nullable=true, options={"unsigned":true})
         */
        private $customer_address_id;

        /**
         * @var \Eccube\Entity\Product
         *
         * @ORM\ManyToOne(targetEntity="Eccube\Entity\Product")
         * @ORM\JoinColumns({
         *   @ORM\JoinColumn(name="product_id", referencedColumnName="id")
         * })
         */
        private $Product;

        /**
         * @var \Eccube\Entity\ProductClass
         *
         * @ORM\ManyToOne(targetEntity="Eccube\Entity\ProductClass")
         * @ORM\JoinColumns({
         *   @ORM\JoinColumn(name="product_class_id", referencedColumnName="id")
         * })
         */
        private $ProductClass;

        /**
         * @var integer
         *
         * @ORM\Column(name="quantity", type="decimal", precision=10, scale=0, options={"default":0})
         */
        private $quantity = 0;

        /**
         * @var smallint|null
         *
         * @ORM\Column(name="repeat_span", type="smallint", nullable=true, options={"unsigned":true})
         */
        public $repeat_span;

        /**
         * @var boolean|null
         *
         * @ORM\Column(name="span_unit", type="boolean", nullable=true, options={"unsigned":true})
         */
        public $span_unit;
        
        /**
         * @var \DateTime|null
         *
         * @ORM\Column(name="contract_date", type="datetimetz", nullable=true)
         */
        public $contract_date;

        /**
         * @var \DateTime|null
         *
         * @ORM\Column(name="prev_delivery_date", type="datetimetz", nullable=true)
         */
        public $prev_delivery_date;

        /**
         * @var \DateTime|null
         *
         * @ORM\Column(name="next_delivery_date", type="datetimetz", nullable=true)
         */
        public $next_delivery_date;

        /**
         * @var \DateTime
         *
         * @ORM\Column(name="create_date", type="datetimetz")
         */
        private $create_date;

        /**
         * @var \DateTime
         *
         * @ORM\Column(name="update_date", type="datetimetz")
         */
        private $update_date;

        /**
         * @return int
         */
        public function getId()
        {
            return $this->id;
        }

        /**
         * Set order.
         *
         * @param \Eccube\Entity\Order|null $order
         *
         * @return SubscriptionContract
         */
        public function setOrder(\Eccube\Entity\Order $order = null)
        {
            $this->Order = $order;

            return $this;
        }

        /**
         * Get customer.
         *
         * @return \Eccube\Entity\Order|null
         */
        public function getOrder()
        {
            return $this->Order;
        }

        /**
         * Set customer.
         *
         * @param \Eccube\Entity\Customer|null $customer
         *
         * @return SubscriptionContract
         */
        public function setCustomer(\Eccube\Entity\Customer $customer = null)
        {
            $this->Customer = $customer;

            return $this;
        }

        /**
         * Get customer.
         *
         * @return \Eccube\Entity\Customer|null
         */
        public function getCustomer()
        {
            return $this->Customer;
        }

        /**
         * set customer_address_id
         *
         * @param int $customer_address_id
         *
         * @return SubscriptionContract
         */
        public function setCustomerAddressId($customer_address_id)
        {
            $this->customer_address_id = $customer_address_id;

            return $this;
        }

        /**
         * get customer_address_id
         *
         * @return int
         */
        public function getCustomerAddressId()
        {
            return $this->customer_address_id;
        }

        /**
         * Set product.
         *
         * @param \Eccube\Entity\Product|null $product
         *
         * @return SubscriptionContract
         */
        public function setProduct(\Eccube\Entity\Product $product = null)
        {
            $this->Product = $product;

            return $this;
        }

        /**
         * Get product.
         *
         * @return \Eccube\Entity\Product|null
         */
        public function getProduct()
        {
            return $this->Product;
        }

        /**
         * Set productClass.
         *
         * @param \Eccube\Entity\ProductClass|null $productClass
         *
         * @return SubscriptionContract
         */
        public function setProductClass(\Eccube\Entity\ProductClass $productClass = null)
        {
            $this->ProductClass = $productClass;

            return $this;
        }

        /**
         * Get productClass.
         *
         * @return \Eccube\Entity\ProductClass|null
         */
        public function getProductClass()
        {
            return $this->ProductClass;
        }

        /**
         * @param  integer  $quantity
         *
         * @return SubscriptionContract
         */
        public function setQuantity($quantity)
        {
            $this->quantity = $quantity;

            return $this;
        }

        /**
         * @return integer
         */
        public function getQuantity()
        {
            return $this->quantity;
        }

        /**
         * set repeat_span.
         *
         * @param smallint $repeat_span
         *
         * @return SubscriptionContract
         */
        public function setRepeatSpan($repeat_span)
        {
            $this->repeat_span = $repeat_span;

            return $this;
        }

        /**
         * get repeat_span.
         *
         * @return smallint
         */
        public function getRepeatSpan()
        {
            return $this->repeat_span;
        }

        /**
         * set span_unit.
         *
         * @param boolean $span_unit
         *
         * @return SubscriptionContract
         */
        public function setSpanUnit($span_unit)
        {
            $this->span_unit = $span_unit;

            return $this;
        }

        /**
         * get span_unit.
         *
         * @return boolean
         */
        public function getSpanUnit()
        {
            return $this->span_unit;
        }

        /**
         * set contract_date.
         *
         * @param \Datetime $contract_date
         *
         * @return SubscriptionContract
         */
        public function setContractDate($contract_date)
        {
            $this->contract_date = $contract_date;

            return $this;
        }

        /**
         * get contract_date.
         *
         * @return \Datetime
         */
        public function getContractDate()
        {
            return $this->contract_date;
        }

        /**
         * set prev_delivery_date.
         *
         * @param \Datetime $prev_delivery_date
         *
         * @return SubscriptionContract
         */
        public function setPrevDeliveryDate($prev_delivery_date)
        {
            $this->prev_delivery_date = $prev_delivery_date;

            return $this;
        }

        /**
         * get prev_delivery_date.
         *
         * @return \Datetime
         */
        public function getPrevDeliveryDate()
        {
            return $this->prev_delivery_date;
        }

        /**
         * set next_delivery_date.
         *
         * @param \Datetime $next_delivery_date
         *
         * @return SubscriptionContract
         */
        public function setNextDeliveryDate($next_delivery_date)
        {
            $this->next_delivery_date = $next_delivery_date;

            return $this;
        }

        /**
         * get next_delivery_date.
         *
         * @return \Datetime
         */
        public function getNextDeliveryDate()
        {
            return $this->next_delivery_date;
        }

        /**
         * set createDate
         *
         * @param \DateTime $createDate
         *
         * @return SubscriptionContract
         */
        public function setCreateDate($createDate)
        {
            $this->create_date = $createDate;

            return $this;
        }

        /**
         * get createDate
         *
         * @return \DateTime
         */
        public function getCreateDate()
        {
            return $this->create_date;
        }

        /**
         * set updateDate
         *
         * @param \DateTime $updateDate
         *
         * @return SubscriptionContract
         */
        public function setUpdateDate($updateDate)
        {
            $this->update_date = $updateDate;

            return $this;
        }

        /**
         * get updateDate
         *
         * @return \DateTime
         */
        public function getUpdateDate()
        {
            return $this->update_date;
        }
    }
}
