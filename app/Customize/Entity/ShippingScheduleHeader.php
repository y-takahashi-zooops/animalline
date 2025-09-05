<?php

namespace Customize\Entity;

use Customize\Repository\ShippingScheduleHeaderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Eccube\Entity\Shipping;
use Eccube\Entity\Order;

/**
 * @ORM\Table(name="ald_shipping_schedule_header")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discriminator_type", type="string", length=255)
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Entity(repositoryClass=ShippingScheduleHeaderRepository::class)
 */
class ShippingScheduleHeader
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id", type="integer")
     */
    private $id;

    /**
     * @ORM\Column(name="shipping_date_schedule", type="date", nullable=true)
     */
    private $shipping_date_schedule;

    /**
     * @ORM\Column(name="arrival_date_schedule", type="date", nullable=true)
     */
    private $arrival_date_schedule;

    /**
     * @ORM\Column(name="arrival_time_code_schedule", type="string", length=2, nullable=true)
     */
    private $arrival_time_code_schedule;

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
     * @ORM\Column(name="total_price", type="integer", nullable=false)
     */
    private $total_price;

    /**
     * @ORM\Column(name="discounted_price", type="integer", nullable=false)
     */
    private $discounted_price;

    /**
     * @ORM\Column(name="tax_price", type="integer", nullable=false)
     */
    private $tax_price;

    /**
     * @ORM\Column(name="postage_price", type="integer", nullable=false)
     */
    private $postage_price;

    /**
     * @ORM\Column(name="total_weight", type="integer", nullable=false)
     */
    private $total_weight;

    /**
     * @ORM\Column(name="wms_ship_no", type="string", length=10, nullable=true)
     */
    private $wms_ship_no;

    /**
     * @ORM\Column(name="shipping_units", type="integer", nullable=true)
     */
    private $shipping_units;

    /**
     * @ORM\Column(name="shipping_date", type="date", nullable=true)
     */
    private $shipping_date;

    /**
     * @ORM\Column(name="delivery_slip_no", type="string", length=20, nullable=true)
     */
    private $delivery_slip_no;

    /**
     * @ORM\Column(name="wms_send_date", type="datetime", nullable=true)
     */
    private $wms_send_date;

    /**
     * @ORM\Column(name="wms_recive_date", type="datetime", nullable=true)
     */
    private $wms_recive_date;

    /**
     * @ORM\Column(name="is_cancel", type="smallint", options={"default" = 0})
     */
    private $is_cancel;

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
     * @ORM\OneToMany(targetEntity=ShippingSchedule::class, mappedBy="ShippingScheduleHeader")
     */
    private $ShippingSchedule;

    /**
     * @ORM\ManyToOne(targetEntity=Order::class, inversedBy="ShippingScheduleHeaders")
     * @ORM\JoinColumn(name="order_id", referencedColumnName="id", nullable=false)
     */
    private $Order;

    /**
     * @ORM\ManyToOne(targetEntity=Shipping::class, inversedBy="ShippingScheduleHeader")
     * @ORM\JoinColumn(name="shipping_id", nullable=false)
     */
    private $Shipping;

    public function __construct()
    {
        $this->ShippingSchedule = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getShippingDateSchedule(): ?\DateTime
    {
        return $this->shipping_date_schedule;
    }

    public function setShippingDateSchedule(\DateTime $shipping_date_schedule): self
    {
        $this->shipping_date_schedule = $shipping_date_schedule;

        return $this;
    }

    public function getArrivalDateSchedule(): ?\DateTime
    {
        return $this->arrival_date_schedule;
    }

    public function setArrivalDateSchedule(\DateTime $arrival_date_schedule): self
    {
        $this->arrival_date_schedule = $arrival_date_schedule;

        return $this;
    }

    public function getArrivalTimeCodeSchedule(): ?string
    {
        return $this->arrival_time_code_schedule;
    }

    public function setArrivalTimeCodeSchedule(?string $arrival_time_code_schedule): self
    {
        $this->arrival_time_code_schedule = $arrival_time_code_schedule;

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

    public function setCustomerZip(string $customer_zip): self
    {
        $this->customer_zip = $customer_zip;

        return $this;
    }

    public function getCustomerAddress(): ?string
    {
        return $this->customer_address;
    }

    public function setCustomerAddress(string $customer_address): self
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

    public function getTotalPrice(): ?int
    {
        return $this->total_price;
    }

    public function setTotalPrice(int $total_price): self
    {
        $this->total_price = $total_price;

        return $this;
    }

    public function getDiscountedPrice(): ?int
    {
        return $this->discounted_price;
    }

    public function setDiscountedPrice(int $discounted_price): self
    {
        $this->discounted_price = $discounted_price;

        return $this;
    }

    public function getTaxPrice(): ?int
    {
        return $this->tax_price;
    }

    public function setTaxPrice(int $tax_price): self
    {
        $this->tax_price = $tax_price;

        return $this;
    }

    public function getPostagePrice(): ?int
    {
        return $this->postage_price;
    }

    public function setPostagePrice(int $postage_price): self
    {
        $this->postage_price = $postage_price;

        return $this;
    }

    public function getTotalWeight(): ?int
    {
        return $this->total_weight;
    }

    public function setTotalWeight(int $total_weight): self
    {
        $this->total_weight = $total_weight;

        return $this;
    }

    public function getWmsShipNo(): ?string
    {
        return $this->wms_ship_no;
    }

    public function setWmsShipNo(?string $wms_ship_no): self
    {
        $this->wms_ship_no = $wms_ship_no;

        return $this;
    }

    public function getShippingUnits(): ?int
    {
        return $this->shipping_units;
    }

    public function setShippingUnits(?int $shipping_units): self
    {
        $this->shipping_units = $shipping_units;

        return $this;
    }

    public function getShippingDate(): ?\DateTime
    {
        return $this->shipping_date;
    }

    public function setShippingDate(?\DateTime $shipping_date): self
    {
        $this->shipping_date = $shipping_date;

        return $this;
    }

    public function getDeliverySlipNo(): ?string
    {
        return $this->delivery_slip_no;
    }

    public function setDeliverySlipNo(?string $delivery_slip_no): self
    {
        $this->delivery_slip_no = $delivery_slip_no;

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
     * @return Collection|ShippingSchedule[]
     */
    public function getShippingSchedule(): Collection
    {
        return $this->ShippingSchedule;
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

    /**
     * @return Order|null
     */
    public function getOrder(): ?Order
    {
        return $this->Order;
    }

    /**
     * @param Order|null $Order
     * @return self
     */
    public function setOrder(?Order $Order): self
    {
        $this->Order = $Order;
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

    public function getCreateDate(): ?\DateTimeInterface
    {
        return $this->create_date;
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

    public function getUpdateDate(): ?\DateTimeInterface
    {
        return $this->update_date;
    }
}
