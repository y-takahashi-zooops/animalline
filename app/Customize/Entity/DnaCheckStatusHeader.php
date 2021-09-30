<?php

namespace Customize\Entity;

use Customize\Repository\DnaCheckStatusHeaderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Eccube\Entity\Master\Pref;

/**
 * @ORM\Entity(repositoryClass=DnaCheckStatusHeaderRepository::class)
 * @ORM\Table(name="ald_dna_check_status_header")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discriminator_type", type="string", length=255)
 * @ORM\HasLifecycleCallbacks()
 */
class DnaCheckStatusHeader
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(name="id", type="integer")
     */
    private $id;

    /**
     * @ORM\Column(name="register_id", type="integer")
     */
    private $register_id;

    /**
     * @ORM\Column(name="pet_id", type="integer", nullable=true)
     */
    private $pet_id;

    /**
     * @ORM\Column(name="site_type", type="smallint")
     */
    private $site_type;

    /**
     * @ORM\Column(name="shipping_status", type="smallint")
     */
    private $shipping_status;

    /**
     * @ORM\Column(name="kit_unit", type="smallint", nullable=true)
     */
    private $kit_unit;

    /**
     * @ORM\Column(name="shipping_name", type="string", length=255, nullable=true)
     */
    private $shipping_name;

    /**
     * @ORM\Column(name="shipping_zip", type="string", length=7, nullable=true)
     */
    private $shipping_zip;

    /**
     * @ORM\ManyToOne(targetEntity="Eccube\Entity\Master\Pref")
     * @ORM\JoinColumn(name="shipping_pref_id", nullable=true)
     */
    private $PrefShipping;

    /**
     * @ORM\Column(name="shipping_pref", type="string", length=20, nullable=true)
     */
    private $shipping_pref;

    /**
     * @ORM\Column(name="shipping_city", type="string", length=255, nullable=true)
     */
    private $shipping_city;

    /**
     * @ORM\Column(name="shipping_address", type="string", length=255, nullable=true)
     */
    private $shipping_address;

    /**
     * @ORM\Column(name="shipping_tel", type="string", length=11, nullable=true)
     */
    private $shipping_tel;

    /**
     * @ORM\Column(name="kit_shipping_date", type="datetime", nullable=true)
     */
    private $kit_shipping_date;

    /**
     * @ORM\Column(name="kit_shipping_operation_date", type="datetime", nullable=true)
     */
    private $kit_shipping_operation_date;

    /**
     * @ORM\Column(name="create_date", type="datetimetz", nullable=true)
     */
    private $create_date;

    /**
     * @ORM\Column(name="update_date", type="datetimetz", nullable=true)
     */
    private $update_date;

    /**
     * @ORM\OneToMany(targetEntity=DnaCheckStatus::class, mappedBy="DnaHeader")
     */
    private $DnaCheckStatus;

    public function __construct()
    {
        $this->DnaCheckStatus = new ArrayCollection();
    }

    public function getDnaCheckStatus(): Collection
    {
        return $this->DnaCheckStatus;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRegisterId(): ?int
    {
        return $this->register_id;
    }

    public function setRegisterId(int $register_id): self
    {
        $this->register_id = $register_id;

        return $this;
    }

    public function getPetId(): ?int
    {
        return $this->pet_id;
    }

    public function setPetId(?int $pet_id): self
    {
        $this->pet_id = $pet_id;

        return $this;
    }

    public function getSiteType(): ?int
    {
        return $this->site_type;
    }

    public function setSiteType(int $site_type): self
    {
        $this->site_type = $site_type;

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

    public function getKitUnit(): ?int
    {
        return $this->kit_unit;
    }

    public function setKitUnit(?int $kit_unit): self
    {
        $this->kit_unit = $kit_unit;

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

    public function getPrefShipping(): ?Pref
    {
        return $this->PrefShipping;
    }

    public function setPrefShipping(?Pref $Pref): self
    {
        $this->PrefShipping = $Pref;

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

    public function setShippingCity(?string $shipping_city): self
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

    public function setCreateDate($createDate): DnaCheckStatusHeader
    {
        $this->create_date = $createDate;

        return $this;
    }

    public function setUpdateDate($updateDate): DnaCheckStatusHeader
    {
        $this->update_date = $updateDate;

        return $this;
    }
}
