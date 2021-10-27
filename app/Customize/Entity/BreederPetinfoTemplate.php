<?php

namespace Customize\Entity;

use Customize\Repository\BreederPetinfoTemplateRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="ald_breeder_petinfo_template")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\Entity(repositoryClass=BreederPetinfoTemplateRepository::class)
 * @ORM\DiscriminatorColumn(name="discriminator_type", type="string", length=255)
 * @ORM\HasLifecycleCallbacks()
 */
class BreederPetinfoTemplate
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id", type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity=Breeders::class, inversedBy="breederPetinfoTemplate", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="breeder_id", nullable=false)
     */
    private $Breeder;

    /**
     * @ORM\Column(name="delivery_way", type="text", nullable=false)
     */
    private $delivery_way;

    /**
     * @ORM\Column(name="payment_method", type="text", nullable=false)
     */
    private $payment_method;

    /**
     * @ORM\Column(name="reservation_fee", type="text", nullable=true)
     */
    private $reservation_fee;

    /**
     * @ORM\Column(name="guarantee", type="text", nullable=true)
     */
    private $guarantee;

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

    public function getBreeder(): ?Breeders
    {
        return $this->Breeder;
    }

    public function setBreeder(Breeders $Breeder): self
    {
        $this->Breeder = $Breeder;

        return $this;
    }

    public function getDeliveryWay(): ?string
    {
        return $this->delivery_way;
    }

    public function setDeliveryWay(string $delivery_way): self
    {
        $this->delivery_way = $delivery_way;

        return $this;
    }

    public function getPaymentMethod(): ?string
    {
        return $this->payment_method;
    }

    public function setPaymentMethod(string $payment_method): self
    {
        $this->payment_method = $payment_method;

        return $this;
    }

    public function getReservationFee(): ?string
    {
        return $this->reservation_fee;
    }

    public function setReservationFee(?string $reservation_fee): self
    {
        $this->reservation_fee = $reservation_fee;

        return $this;
    }

    public function getGuarantee(): ?string
    {
        return $this->guarantee;
    }

    public function setGuarantee(?string $guarantee): self
    {
        $this->guarantee = $guarantee;

        return $this;
    }

    public function setCreateDate(?\DateTime $createDate): self
    {
        $this->create_date = $createDate;

        return $this;
    }

    public function setUpdateDate(?\DateTime $updateDate): self
    {
        $this->update_date = $updateDate;

        return $this;
    }
}
