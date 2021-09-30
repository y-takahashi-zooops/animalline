<?php

namespace Customize\Entity;

use Customize\Repository\StockWasteRepository;
use Doctrine\ORM\Mapping as ORM;
use Eccube\Entity\Product;
use Eccube\Entity\ProductClass;

/**
 * @ORM\Table(name="ald_stock_waste")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discriminator_type", type="string", length=255)
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Entity(repositoryClass=StockWasteRepository::class)
 */
class StockWaste
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(name="id", type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Product::class, inversedBy="StockWaste")
     * @ORM\JoinColumn(name="product_id", nullable=true)
     */
    private $Product;

    /**
     * @ORM\ManyToOne(targetEntity=ProductClass::class, inversedBy="StockWaste")
     * @ORM\JoinColumn(name="product_class_id", nullable=false)
     */
    private $ProductClass;

    /**
     * @ORM\Column(name="waste_date", type="date", nullable=true)
     */
    private $waste_date;

    /**
     * @ORM\Column(name="waste_unit", type="integer", nullable=true)
     */
    private $waste_unit;

    /**
     * @ORM\ManyToOne(targetEntity=StockWasteReason::class, inversedBy="StockWaste")
     * @ORM\JoinColumn(name="waste_reason_id", nullable=true)
     */
    private $StockWasteReason;

    /**
     * @ORM\Column(name="comment", type="text", nullable=true)
     */
    private $comment;

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

    public function getProduct(): ?Product
    {
        return $this->Product;
    }

    public function setProductId(?Product $Product): self
    {
        $this->Product = $Product;

        return $this;
    }

    public function getProductClass(): ?ProductClass
    {
        return $this->ProductClass;
    }

    public function setProductClassId(?ProductClass $ProductClass): self
    {
        $this->ProductClass = $ProductClass;

        return $this;
    }

    public function getWasteDate(): ?\DateTimeInterface
    {
        return $this->waste_date;
    }

    public function setWasteDate(?\DateTimeInterface $waste_date): self
    {
        $this->waste_date = $waste_date;

        return $this;
    }

    public function getWasteUnit(): ?int
    {
        return $this->waste_unit;
    }

    public function setWasteUnit(int $waste_unit): self
    {
        $this->waste_unit = $waste_unit;

        return $this;
    }

    public function getStockWasteReason(): ?StockWasteReason
    {
        return $this->StockWasteReason;
    }

    public function setStockWasteReason(?StockWasteReason $StockWasteReason): self
    {
        $this->StockWasteReason = $StockWasteReason;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

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
