<?php

namespace Customize\Entity;

use Customize\Repository\ShippingScheduleRepository;
use Doctrine\ORM\Mapping as ORM;
use Eccube\Entity\OrderItem;
use Eccube\Entity\ProductClass;

/**
 * @ORM\Table(name="ald_shipping_schedule")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discriminator_type", type="string", length=255)
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Entity(repositoryClass=ShippingScheduleRepository::class)
 */
class ShippingSchedule
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id", type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=ShippingScheduleHeader::class, inversedBy="ShippingSchedule")
     * @ORM\JoinColumn(name="header_id", nullable=false)
     */
    private $ShippingScheduleHeader;

    /**
     * @ORM\ManyToOne(targetEntity=ProductClass::class, inversedBy="ShippingSchedules")
     * @ORM\JoinColumn(name="product_class_id", nullable=false)
     */
    private $ProductClass;

    /**
     * @ORM\Column(name="warehouse_code", type="string", length=5)
     */
    private $warehouse_code;

    /**
     * @ORM\Column(name="item_code_01", type="string", length=20)
     */
    private $item_code_01;

    /**
     * @ORM\Column(name="item_code_02", type="string", length=4, nullable=true)
     */
    private $item_code_02;

    /**
     * @ORM\Column(name="jan_code", type="string", length=13, nullable=true)
     */
    private $jan_code;

    /**
     * @ORM\Column(name="quantity", type="string", length=255, nullable=false)
     */
    private $quantity;

    /**
     * @ORM\Column(name="standerd_price", type="integer", nullable=false)
     */
    private $standerd_price;

    /**
     * @ORM\Column(name="selling_price", type="integer", nullable=false)
     */
    private $selling_price;

    /**
     * @ORM\ManyToOne(targetEntity=OrderItem::class, inversedBy="ShippingSchedules")
     * @ORM\JoinColumn(name="order_detail_id", nullable=false)
     */
    private $OrderDetail;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="create_date", type="datetimetz", nullable=true)
     */
    private $create_date;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="update_date", type="datetimetz", nullable=true)
     */
    private $update_date;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getShippingScheduleHeader(): ?ShippingScheduleHeader
    {
        return $this->ShippingScheduleHeader;
    }

    public function setShippingScheduleHeader(?ShippingScheduleHeader $ShippingScheduleHeader): self
    {
        $this->ShippingScheduleHeader = $ShippingScheduleHeader;

        return $this;
    }

    public function getWarehouseCode(): ?string
    {
        return $this->warehouse_code;
    }

    public function setWarehouseCode(string $warehouse_code): self
    {
        $this->warehouse_code = $warehouse_code;

        return $this;
    }

    public function getItemCode01(): ?string
    {
        return $this->item_code_01;
    }

    public function setItemCode01(string $item_code_01): self
    {
        $this->item_code_01 = $item_code_01;

        return $this;
    }

    public function getItemCode02(): ?string
    {
        return $this->item_code_02;
    }

    public function setItemCode02(?string $item_code_02): self
    {
        $this->item_code_02 = $item_code_02;

        return $this;
    }

    public function getJanCode(): ?string
    {
        return $this->jan_code;
    }

    public function setJanCode(?string $jan_code): self
    {
        $this->jan_code = $jan_code;

        return $this;
    }

    public function getQuantity(): ?string
    {
        return $this->quantity;
    }

    public function setQuantity(string $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getStanderdPrice(): ?int
    {
        return $this->standerd_price;
    }

    public function setStanderdPrice(int $standerd_price): self
    {
        $this->standerd_price = $standerd_price;

        return $this;
    }

    public function getSellingPrice(): ?int
    {
        return $this->selling_price;
    }

    public function setSellingPrice(int $selling_price): self
    {
        $this->selling_price = $selling_price;

        return $this;
    }

    public function getOrderDetail(): ?OrderItem
    {
        return $this->OrderDetail;
    }

    public function setOrderDetail(?OrderItem $order_detail_id): self
    {
        $this->OrderDetail = $order_detail_id;

        return $this;
    }

    public function getProductClass(): ?ProductClass
    {
        return $this->ProductClass;
    }

    public function setProductClass(?ProductClass $product_class_id): self
    {
        $this->ProductClass = $product_class_id;

        return $this;
    }

    /**
     * Set createDate.
     *
     * @param \DateTime $createDate
     *
     * @return Payment
     */
    public function setCreateDate($createDate)
    {
        $this->create_date = $createDate;

        return $this;
    }

    /**
     * Set updateDate.
     *
     * @param \DateTime $updateDate
     *
     * @return Payment
     */
    public function setUpdateDate($updateDate)
    {
        $this->update_date = $updateDate;

        return $this;
    }
}
