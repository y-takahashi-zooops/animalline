<?php

namespace Customize\Entity;

use Customize\Repository\ItemWasteRepository;
use Doctrine\ORM\Mapping as ORM;
use Eccube\Entity\Product;

/**
 * @ORM\Table(name="ald_item_waste")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discriminator_type", type="string", length=255)
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Entity(repositoryClass=ItemWasteRepository::class)
 */
class ItemWaste
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id", type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Product::class, inversedBy="ItemWastes")
     * @ORM\JoinColumn(name="product_id", nullable=false)
     */
    private $Product;

    /**
     * @ORM\Column(name="waste_date", type="date", nullable=true)
     */
    private $waste_date;

    /**
     * @ORM\Column(name="waste_quantity", type="smallint", nullable=true)
     */
    private $waste_quantity;

    /**
     * @ORM\Column(name="waste_reason", type="smallint", nullable=true)
     */
    private $waste_reason;

    /**
     * @ORM\Column(name="remark_text", type="text", nullable=true)
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

    public function getProduct(): ?Product
    {
        return $this->Product;
    }

    public function setProduct(?Product $Product): self
    {
        $this->Product = $Product;

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

    public function getWasteQuantity(): ?int
    {
        return $this->waste_quantity;
    }

    public function setWasteQuantity(?int $waste_quantity): self
    {
        $this->waste_quantity = $waste_quantity;

        return $this;
    }

    public function getWasteReason(): ?int
    {
        return $this->waste_reason;
    }

    public function setWasteReason(?int $waste_reason): self
    {
        $this->waste_reason = $waste_reason;

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
}
