<?php

namespace Customize\Entity;

use Customize\Repository\InstockScheduleRepository;
use Doctrine\ORM\Mapping as ORM;
use Eccube\Entity\ProductClass;

/**
 * @ORM\Table(name="ald_instock_schedule")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discriminator_type", type="string", length=255)
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Entity(repositoryClass=InstockScheduleRepository::class)
 */
class InstockSchedule
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(name="id", type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=InstockScheduleHeader::class, inversedBy="InstockSchedule")
     * @ORM\JoinColumn(name="header_id", nullable=true)
     */
    private $InstockHeader;

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
     * @ORM\Column(name="purchase_price", type="decimal", precision=12, scale=2, nullable=false)
     */
    private $purchase_price;

    /**
     * @ORM\Column(name="arrival_quantity_schedule", type="smallint", nullable=false)
     */
    private $arrival_quantity_schedule;

    /**
     * @ORM\Column(name="arrival_quantity", type="smallint", nullable=true)
     */
    private $arrival_quantity;

    /**
     * @ORM\ManyToOne(targetEntity=ProductClass::class)
     * @ORM\JoinColumn(name="product_class_id", nullable=false)
     */
    private $ProductClass;

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

    public function getInstockHeader(): ?InstockScheduleHeader
    {
        return $this->InstockHeader;
    }

    public function setInstockHeader(?InstockScheduleHeader $InstockHeader): self
    {
        $this->InstockHeader = $InstockHeader;

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

    public function getPurchasePrice(): ?string
    {
        return $this->purchase_price;
    }

    public function setPurchasePrice(string $purchase_price): self
    {
        $this->purchase_price = $purchase_price;

        return $this;
    }

    public function getArrivalQuantitySchedule(): ?int
    {
        return $this->arrival_quantity_schedule;
    }

    public function setArrivalQuantitySchedule(int $arrival_quantity_schedule): self
    {
        $this->arrival_quantity_schedule = $arrival_quantity_schedule;

        return $this;
    }

    public function getArrivalQuantity(): ?int
    {
        return $this->arrival_quantity;
    }

    public function setArrivalQuantity(?int $arrival_quantity): self
    {
        $this->arrival_quantity = $arrival_quantity;

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

    /**
     * @var string
     *
     */
    private $product_name;

    /**
     * @var int
     *
     */
    private $price = 0;

    /**
     * @var int
     *
     */
    private $quantity = 0;

    /**
     * @var int
     *
     */
    private $tax_rate = 0;

    /**
     * @var \Eccube\Entity\Master\TaxType
     *
     */
    private $TaxType;

    /**
     * Set productName.
     *
     * @param string $productName
     *
     * @return InstockSchedule
     */
    public function setProductName($productName)
    {
        $this->product_name = $productName;

        return $this;
    }

    /**
     * Get productName.
     *
     * @return string
     */
    public function getProductName()
    {
        return $this->product_name;
    }

    /**
     * Set price.
     *
     * @param string $price
     *
     * @return InstockSchedule
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price.
     *
     * @return string
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set quantity.
     *
     * @param string $quantity
     *
     * @return InstockSchedule
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * Get quantity.
     *
     * @return string
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * Set taxRate.
     *
     * @param string $taxRate
     *
     * @return InstockSchedule
     */
    public function setTaxRate($taxRate)
    {
        $this->tax_rate = $taxRate;

        return $this;
    }

    /**
     * Get taxRate.
     *
     * @return string
     */
    public function getTaxRate()
    {
        return $this->tax_rate;
    }

    /**
     * Set taxType
     *
     * @param \Eccube\Entity\Master\TaxType $taxType
     *
     * @return InstockSchedule
     */
    public function setTaxType(\Eccube\Entity\Master\TaxType $taxType = null)
    {
        $this->TaxType = $taxType;

        return $this;
    }

    /**
     * Get taxType
     *
     * @return \Eccube\Entity\Master\TaxType
     */
    public function getTaxType()
    {
        return $this->TaxType;
    }
}
