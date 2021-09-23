<?php

namespace Customize\Entity;

use Customize\Repository\InstockScheduleHeaderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="ald_instock_schedule_header")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discriminator_type", type="string", length=255)
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Entity(repositoryClass=InstockScheduleHeaderRepository::class)
 */
class InstockScheduleHeader
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(name="id", type="integer")
     */
    private $id;

    /**
     * @ORM\Column(name="order_date", type="date", nullable=false)
     */
    private $order_date;

    /**
     * @ORM\Column(name="supplier_code", type="string", length=6, nullable=false)
     */
    private $supplier_code;

    /**
     * @ORM\Column(name="arrival_date_schedule", type="date", nullable=false)
     */
    private $arrival_date_schedule;

    /**
     * @ORM\Column(name="arrival_date", type="date", nullable=true)
     */
    private $arrival_date;

    /**
     * @ORM\Column(name="remark_text", type="string", length=128, nullable=true)
     */
    private $remark_text;

    /**
     * @ORM\Column(name="is_cancel", type="smallint", nullable=false, options={"default" = 0})
     */
    private $is_cancel = 0;

    /**
     * @ORM\Column(name="cancel_reason", type="smallint", nullable=true)
     */
    private $cancel_reason;

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

    /**
     * @ORM\OneToMany(targetEntity=InstockSchedule::class, mappedBy="InstockHeader")
     */
    private $InstockSchedule;

    public function __construct()
    {
        $this->InstockSchedule = new ArrayCollection();
    }

    /**
     * @return Collection|InstockSchedule[]
     */
    public function getInstockSchedule(): Collection
    {
        return $this->InstockSchedule;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrderDate(): ?\DateTimeInterface
    {
        return $this->order_date;
    }

    public function setOrderDate(\DateTimeInterface $order_date): self
    {
        $this->order_date = $order_date;

        return $this;
    }

    public function getSupplierCode(): ?string
    {
        return $this->supplier_code;
    }

    public function setSupplierCode(string $supplier_code): self
    {
        $this->supplier_code = $supplier_code;

        return $this;
    }

    public function getArrivalDateSchedule(): ?\DateTimeInterface
    {
        return $this->arrival_date_schedule;
    }

    public function setArrivalDateSchedule(\DateTimeInterface $arrival_date_schedule): self
    {
        $this->arrival_date_schedule = $arrival_date_schedule;

        return $this;
    }

    public function getArrivalDate(): ?\DateTimeInterface
    {
        return $this->arrival_date;
    }

    public function setArrivalDate(?\DateTimeInterface $arrival_date): self
    {
        $this->arrival_date = $arrival_date;

        return $this;
    }

    public function getRemarkText(): ?string
    {
        return $this->remark_text;
    }

    public function setRemarkText(string $remark_text): self
    {
        $this->remark_text = $remark_text;

        return $this;
    }

    public function getIsCancel(): ?int
    {
        return $this->is_cancel;
    }

    public function setIsCancel(int $is_cancel): self
    {
        $this->is_cancel = $is_cancel;

        return $this;
    }

    public function getCancelReason(): ?int
    {
        return $this->cancel_reason;
    }

    public function setCancelReason(?int $cancel_reason): self
    {
        $this->cancel_reason = $cancel_reason;

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
