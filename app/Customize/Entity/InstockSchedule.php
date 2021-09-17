<?php

namespace Customize\Entity;

use Customize\Repository\InstockScheduleRepository;
use Doctrine\ORM\Mapping as ORM;

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
     * @ORM\Column(name="arrival_box_schedule", type="smallint", nullable=false)
     */
    private $arrival_box_schedule;

    /**
     * @ORM\Column(name="arrival_box_", type="smallint", nullable=true)
     */
    private $arrival_box_;

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

    public function getBInstockHeader(): ?InstockScheduleHeader
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

    public function getArrivalBoxSchedule(): ?int
    {
        return $this->arrival_box_schedule;
    }

    public function setArrivalBoxSchedule(int $arrival_box_schedule): self
    {
        $this->arrival_box_schedule = $arrival_box_schedule;

        return $this;
    }

    public function getArrivalBox(): ?int
    {
        return $this->arrival_box_;
    }

    public function setArrivalBox(?int $arrival_box_): self
    {
        $this->arrival_box_ = $arrival_box_;

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
