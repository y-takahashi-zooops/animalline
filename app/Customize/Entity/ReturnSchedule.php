<?php

namespace Customize\Entity;

use Customize\Repository\ReturnScheduleRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Eccube\Entity\OrderItem;

/**
 * @ORM\Table(name="ald_return_schedule")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discriminator_type", type="string", length=255)
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Entity(repositoryClass=ReturnScheduleRepository::class)
 */
class ReturnSchedule
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(name="id", type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=ReturnScheduleHeader::class, inversedBy="ReturnSchedule")
     * @ORM\JoinColumn(name="header_id", nullable=false)
     */
    private $ReturnScheduleHeader;

    /**
     * @ORM\Column(name="product_class_id", type="integer", nullable=true)
     */
    private $product_class_id;

    /**
     * @ORM\Column(name="warehouse_code", type="string", length=5, nullable=false)
     */
    private $warehouse_code;

    /**
     * @ORM\Column(name="item_code_01", type="string", length=20, nullable=false)
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
     * @ORM\Column(name="quantity_schedule", type="smallint", nullable=false)
     */
    private $quantity_schedule;

    /**
     * @ORM\Column(name="quantity", type="smallint", nullable=true)
     */
    private $quantity;

    /**
     * @ORM\Column(name="cost_price", type="integer", nullable=false)
     */
    private $cost_price;

    /**
     * @ORM\Column(name="selling_price", type="integer", nullable=false)
     */
    private $selling_price;

    /**
     * @ORM\ManyToOne(targetEntity=OrderItem::class, inversedBy="ReturnSchedule")
     * @ORM\JoinColumn(name="order_detail_id", nullable=false)
     */
    private $OrderItem;

    /**
     * @ORM\Column(name="remark_text", type="string", length=128, nullable=true)
     */
    private $remark_text;

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

    public function getReturnScheduleHeader(): ?ReturnScheduleHeader
    {
        return $this->ReturnScheduleHeader;
    }

    public function setReturnScheduleHeader(?ReturnScheduleHeader $ReturnScheduleHeader): self
    {
        $this->ReturnScheduleHeader = $ReturnScheduleHeader;

        return $this;
    }

    public function getProductClassId(): ?int
    {
        return $this->product_class_id;
    }

    public function setProductClassId(?int $product_class_id): self
    {
        $this->product_class_id = $product_class_id;

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

    public function getQuantitySchedule(): ?int
    {
        return $this->quantity_schedule;
    }

    public function setQuantitySchedule(int $quantity_schedule): self
    {
        $this->quantity_schedule = $quantity_schedule;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(?int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getCostPrice(): ?int
    {
        return $this->cost_price;
    }

    public function setCostPrice(int $cost_price): self
    {
        $this->cost_price = $cost_price;

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

    public function getRemarkText(): ?string
    {
        return $this->remark_text;
    }

    public function setRemarkText(?string $remark_text): self
    {
        $this->remark_text = $remark_text;

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

    public function getOrderItem(): ?OrderItem
    {
        return $this->OrderItem;
    }

    public function setOrderItem(?OrderItem $OrderItem): self
    {
        $this->OrderItem = $OrderItem;

        return $this;
    }
}
