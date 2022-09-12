<?php

namespace Customize\Entity;

use Customize\Repository\BenefitsStatusRepository;
use Doctrine\ORM\Mapping as ORM;
use Eccube\Entity\Master\Pref;

/**
 * @ORM\Entity(repositoryClass=BenefitsStatusRepository::class)
 * @ORM\Table(name="ald_benefits_status")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discriminator_type", type="string", length=255)
 * @ORM\HasLifecycleCallbacks()
 */
class BenefitsStatus
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(name="id", type="integer")
     */
    private $id;

    /**
     * @ORM\Column(name="register_id", type="integer", nullable=false)
     */
    private $register_id;

    /**
     * @ORM\Column(name="pet_id", type="integer", nullable=true)
     */
    private $pet_id;

    /**
     * @ORM\Column(name="site_type", type="smallint", nullable=false)
     */
    private $site_type;

    /**
     * @ORM\Column(name="shipping_status", type="smallint", nullable=false)
     */
    private $shipping_status;

    /**
     * @ORM\Column(name="shipping_name", type="string", length=255, nullable=true)
     */
    private $shipping_name;

    /**
     * @ORM\Column(name="shipping_zip", type="string", length=7, nullable=true)
     */
    private $shipping_zip;

    /**
     * @var \Eccube\Entity\Master\Pref
     *
     * @ORM\ManyToOne(targetEntity="Eccube\Entity\Master\Pref")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="shipping_pref_id", referencedColumnName="id")
     * })
     */
    private $Pref;

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
     * @ORM\Column(name="benefits_shipping_date", type="datetime", nullable=true)
     */
    private $benefits_shipping_date;

    /**
     * @ORM\Column(name="benefits_type", type="smallint", nullable=false)
     */
    private $benefits_type;

    /**
     * @ORM\Column(name="benefits_shipping_operation_date", type="datetime", nullable=true)
     */
    private $benefits_shipping_operation_date;

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

    public function getBenefitsType(): ?int
    {
        return $this->benefits_type;
    }

    public function setBenefitsType(int $benefits_type): self
    {
        $this->benefits_type = $benefits_type;

        return $this;
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

    public function setPetId(int $pet_id): self
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

    public function setShippingZip(string $shipping_zip): self
    {
        $this->shipping_zip = $shipping_zip;

        return $this;
    }

    public function getPref(): ?Pref
    {
        return $this->Pref;
    }

    public function setPref(?Pref $Pref): self
    {
        $this->Pref = $Pref;

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

    public function getBenefitsShippingDate(): ?\DateTimeInterface
    {
        return $this->benefits_shipping_date;
    }

    public function setBenefitsShippingDate(?\DateTimeInterface $benefits_shipping_date): self
    {
        $this->benefits_shipping_date = $benefits_shipping_date;

        return $this;
    }

    public function getBenefitsShippingOperationDate(): ?\DateTimeInterface
    {
        return $this->benefits_shipping_operation_date;
    }

    public function setBenefitsShippingOperationDate(?\DateTimeInterface $benefits_shipping_operation_date): self
    {
        $this->benefits_shipping_operation_date = $benefits_shipping_operation_date;

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
