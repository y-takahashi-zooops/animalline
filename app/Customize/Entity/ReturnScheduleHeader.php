<?php

namespace Customize\Entity;

use Customize\Repository\ReturnScheduleHeaderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Eccube\Entity\Order;

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
     * @ORM\ManyToOne(targetEntity=Order::class, inversedBy="ReturnScheduleHeader")
     * @ORM\JoinColumn(name="order_id", nullable=false)
     */
    private $Order;

    /**
     * @ORM\Column(name="return_date_schedule", type="date", nullable=false)
     */
    private $return_date_schedule;

    /**
     * @ORM\Column(name="return_date", type="date", nullable=true)
     */
    private $return_date;

    /**
     * @ORM\Column(name="shop_code", type="string", length=12, nullable=true)
     */
    private $shop_code;

    /**
     * @ORM\Column(name="customer_name", type="string", length=40, nullable=false)
     */
    private $customer_name;

    /**
     * @ORM\Column(name="customer_zip", type="string", length=8, nullable=false)
     */
    private $customer_zip;

    /**
     * @ORM\Column(name="customer_address", type="string", length=80, nullable=false)
     */
    private $customer_address;

    /**
     * @ORM\Column(name="customer_tel", type="string", length=20, nullable=false)
     */
    private $customer_tel;

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

    public function getOrder(): ?Order
    {
        return $this->Order;
    }

    public function setOrder(?Order $Order): self
    {
        $this->Order = $Order;

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

    public function getReturnDate(): ?\DateTimeInterface
    {
        return $this->return_date;
    }

    public function setReturnDate(\DateTimeInterface $return_date): self
    {
        $this->return_date = $return_date;

        return $this;
    }

    public function getShopCode(): ?string
    {
        return $this->shop_code;
    }

    public function setShopCode(?string $shop_code): self
    {
        $this->shop_code = $shop_code;

        return $this;
    }

    public function getCustomerName(): ?string
    {
        return $this->customer_name;
    }

    public function setCustomerName(string $customer_name): self
    {
        $this->customer_name = $customer_name;

        return $this;
    }

    public function getCustomerZip(): ?string
    {
        return $this->customer_zip;
    }

    public function setCustomerZip(?string $customer_zip): self
    {
        $this->customer_zip = $customer_zip;

        return $this;
    }

    public function getCustomerAddress(): ?string
    {
        return $this->customer_address;
    }

    public function setCustomerAddress(?string $customer_address): self
    {
        $this->customer_address = $customer_address;

        return $this;
    }

    public function getCustomerTel(): ?string
    {
        return $this->customer_tel;
    }

    public function setCustomerTel(string $customer_tel): self
    {
        $this->customer_tel = $customer_tel;

        return $this;
    }
}
