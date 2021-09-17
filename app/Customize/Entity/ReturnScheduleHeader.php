<?php

namespace Customize\Entity;

use Customize\Repository\ReturnScheduleHeaderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Eccube\Entity\Shipping;

/**
 * @ORM\Table(name="ald_return_schedule_header")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discriminator_type", type="string", length=255)
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Entity(repositoryClass=ReturnScheduleHeaderRepository::class)
 */
class ReturnScheduleHeader
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(name="id", type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Shipping::class, inversedBy="ReturnScheduleHeader")
     * @ORM\JoinColumn(name="shipping_id", nullable=false)
     */
    private $Shipping;

    /**
     * @ORM\Column(name="return_date_schedule", type="date", nullable=false)
     */
    private $return_date_schedule;

    /**
     * @ORM\Column(name="wms_send_date", type="datetime", nullable=true)
     */
    private $wms_send_date;

    /**
     * @ORM\Column(name="wms_recive_date", type="datetime", nullable=true)
     */
    private $wms_recive_date;

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
     * @ORM\OneToMany(targetEntity=ReturnSchedule::class, mappedBy="ReturnScheduleHeader")
     */
    private $ReturnSchedule;

    public function __construct()
    {
        $this->ReturnSchedule = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getShipping(): ?Shipping
    {
        return $this->Shipping;
    }

    public function setShipping(?Shipping $Shipping): self
    {
        $this->Shipping = $Shipping;

        return $this;
    }

    public function getReturnDateSchedule(): ?\DateTimeInterface
    {
        return $this->return_date_schedule;
    }

    public function setReturnDateSchedule(\DateTimeInterface $return_date_schedule): self
    {
        $this->return_date_schedule = $return_date_schedule;

        return $this;
    }

    public function getWmsSendDate(): ?\DateTimeInterface
    {
        return $this->wms_send_date;
    }

    public function setWmsSendDate(?\DateTimeInterface $wms_send_date): self
    {
        $this->wms_send_date = $wms_send_date;

        return $this;
    }

    public function getWmsReciveDate(): ?\DateTimeInterface
    {
        return $this->wms_recive_date;
    }

    public function setWmsReciveDate(?\DateTimeInterface $wms_recive_date): self
    {
        $this->wms_recive_date = $wms_recive_date;

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
     * @return Collection|ReturnSchedule[]
     */
    public function getReturnSchedule(): Collection
    {
        return $this->ReturnSchedule;
    }
}
