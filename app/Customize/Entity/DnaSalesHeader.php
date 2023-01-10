<?php

namespace Customize\Entity;

use Customize\Repository\DnaSalesHeaderRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="ald_dna_sales_header")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\Entity(repositoryClass=DnaSalesHeaderRepository::class)
 * @ORM\DiscriminatorColumn(name="discriminator_type", type="string", length=255)
 * @ORM\HasLifecycleCallbacks()
 */
class DnaSalesHeader
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $customer_id;

    /**
     * @ORM\Column(type="smallint")
     */
    private $shipping_status;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $shipping_name;

    /**
     * @ORM\Column(type="string", length=7, nullable=true)
     */
    private $shipping_zip;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $shipping_pref_id;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $shipping_pref;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $shipping_city;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $shipping_address;

    /**
     * @ORM\Column(type="string", length=11, nullable=true)
     */
    private $shipping_tel;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $total_price;

    /**
     * @ORM\Column(type="integer", nullable=true, options={"default" : 0})
     */
    private $order_id;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $kit_shipping_date;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $kit_shipping_operation_date;

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

    public function getCustomerId(): ?int
    {
        return $this->customer_id;
    }

    public function setCustomerId(int $customer_id): self
    {
        $this->customer_id = $customer_id;

        return $this;
    }

    public function getShippingStatus(): ?int
    {
        return $this->shipping_status;
    }

    public function setShippingStatus(int $shipping_status): self
    {
        $this->shipping_status = $shipping_status;

        return $this;
    }

    public function getShippingName(): ?string
    {
        return $this->shipping_name;
    }

    public function setShippingName(?string $shipping_name): self
    {
        $this->shipping_name = $shipping_name;

        return $this;
    }

    public function getShippingZip(): ?string
    {
        return $this->shipping_zip;
    }

    public function setShippingZip(?string $shipping_zip): self
    {
        $this->shipping_zip = $shipping_zip;

        return $this;
    }

    public function getShippingPrefId(): ?int
    {
        return $this->shipping_pref_id;
    }

    public function setShippingPrefId(?int $shipping_pref_id): self
    {
        $this->shipping_pref_id = $shipping_pref_id;

        return $this;
    }

    public function getShippingPref(): ?string
    {
        return $this->shipping_pref;
    }

    public function setShippingPref(?string $shipping_pref): self
    {
        $this->shipping_pref = $shipping_pref;

        return $this;
    }

    public function getShippingCity(): ?string
    {
        return $this->shipping_city;
    }

    public function setShippingCity(string $shipping_city): self
    {
        $this->shipping_city = $shipping_city;

        return $this;
    }

    public function getShippingAddress(): ?string
    {
        return $this->shipping_address;
    }

    public function setShippingAddress(?string $shipping_address): self
    {
        $this->shipping_address = $shipping_address;

        return $this;
    }

    public function getShippingTel(): ?string
    {
        return $this->shipping_tel;
    }

    public function setShippingTel(?string $shipping_tel): self
    {
        $this->shipping_tel = $shipping_tel;

        return $this;
    }

    public function getTotalPrice(): ?int
    {
        return $this->total_price;
    }

    public function setTotalPrice(?int $total_price): self
    {
        $this->total_price = $total_price;

        return $this;
    }

    public function getOrderId(): ?int
    {
        return $this->order_id;
    }

    public function setOrderId(?int $order_id): self
    {
        $this->order_id = $order_id;

        return $this;
    }

    public function getKitShippingDate(): ?\DateTimeInterface
    {
        return $this->kit_shipping_date;
    }

    public function setKitShippingDate(?\DateTimeInterface $kit_shipping_date): self
    {
        $this->kit_shipping_date = $kit_shipping_date;

        return $this;
    }

    public function getKitShippingOperationDate(): ?\DateTimeInterface
    {
        return $this->kit_shipping_operation_date;
    }

    public function setKitShippingOperationDate(?\DateTimeInterface $kit_shipping_operation_date): self
    {
        $this->kit_shipping_operation_date = $kit_shipping_operation_date;

        return $this;
    }

    public function getCreateDate(): ?\DateTimeInterface
    {
        return $this->create_date;
    }

    /**
     * Set createDate.
     *
     * @param ?\DateTime $createDate
     *
     * @return self
     */
    public function setCreateDate(?\DateTime $createDate): self
    {
        $this->create_date = $createDate;

        return $this;
    }

    public function getUpdateDate(): ?\DateTimeInterface
    {
        return $this->update_date;
    }

    /**
     * Set updateDate.
     *
     * @param ?\DateTime $updateDate
     *
     * @return self
     */
    public function setUpdateDate(?\DateTime $updateDate): self
    {
        $this->update_date = $updateDate;

        return $this;
    }
}
