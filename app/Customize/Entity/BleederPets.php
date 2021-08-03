<?php

namespace Customize\Entity;

use Customize\Repository\BleederPetsRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=BleederPetsRepository::class)
 */
class BleederPets
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
    private $bleeder_id;

    /**
     * @ORM\Column(type="smallint")
     */
    private $pet_kind;

    /**
     * @ORM\Column(type="integer")
     */
    private $breeds_type;

    /**
     * @ORM\Column(type="smallint")
     */
    private $pet_sex;

    /**
     * @ORM\Column(type="date")
     */
    private $pet_birthday;

    /**
     * @ORM\Column(type="integer")
     */
    private $coat_color;

    /**
     * @ORM\Column(type="smallint")
     */
    private $future_wait;

    /**
     * @ORM\Column(type="integer")
     */
    private $dna_check_result;

    /**
     * @ORM\Column(type="text")
     */
    private $pr_comment;

    /**
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @ORM\Column(type="smallint")
     */
    private $is_breeding;

    /**
     * @ORM\Column(type="smallint")
     */
    private $is_selling;

    /**
     * @ORM\Column(type="text")
     */
    private $guarantee;

    /**
     * @ORM\Column(type="smallint")
     */
    private $is_pedigree;

    /**
     * @ORM\Column(type="smallint")
     */
    private $include_vaccine_fee;

    /**
     * @ORM\Column(type="text")
     */
    private $delivery_time;

    /**
     * @ORM\Column(type="text")
     */
    private $delivery_way;

    /**
     * @ORM\Column(type="text")
     */
    private $payment_method;

    /**
     * @ORM\Column(type="integer")
     */
    private $reservation_fee;

    /**
     * @ORM\Column(type="integer")
     */
    private $price;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBleederId(): ?int
    {
        return $this->bleeder_id;
    }

    public function setBleederId(int $bleeder_id): self
    {
        $this->bleeder_id = $bleeder_id;

        return $this;
    }

    public function getPetKind(): ?int
    {
        return $this->pet_kind;
    }

    public function setPetKind(int $pet_kind): self
    {
        $this->pet_kind = $pet_kind;

        return $this;
    }

    public function getBreedsType(): ?int
    {
        return $this->breeds_type;
    }

    public function setBreedsType(int $breeds_type): self
    {
        $this->breeds_type = $breeds_type;

        return $this;
    }

    public function getPetSex(): ?int
    {
        return $this->pet_sex;
    }

    public function setPetSex(int $pet_sex): self
    {
        $this->pet_sex = $pet_sex;

        return $this;
    }

    public function getPetBirthday(): ?\DateTimeInterface
    {
        return $this->pet_birthday;
    }

    public function setPetBirthday(\DateTimeInterface $pet_birthday): self
    {
        $this->pet_birthday = $pet_birthday;

        return $this;
    }

    public function getCoatColor(): ?int
    {
        return $this->coat_color;
    }

    public function setCoatColor(int $coat_color): self
    {
        $this->coat_color = $coat_color;

        return $this;
    }

    public function getFutureWait(): ?int
    {
        return $this->future_wait;
    }

    public function setFutureWait(int $future_wait): self
    {
        $this->future_wait = $future_wait;

        return $this;
    }

    public function getDnaCheckResult(): ?int
    {
        return $this->dna_check_result;
    }

    public function setDnaCheckResult(int $dna_check_result): self
    {
        $this->dna_check_result = $dna_check_result;

        return $this;
    }

    public function getPrComment(): ?string
    {
        return $this->pr_comment;
    }

    public function setPrComment(string $pr_comment): self
    {
        $this->pr_comment = $pr_comment;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getIsBreeding(): ?int
    {
        return $this->is_breeding;
    }

    public function setIsBreeding(int $is_breeding): self
    {
        $this->is_breeding = $is_breeding;

        return $this;
    }

    public function getIsSelling(): ?int
    {
        return $this->is_selling;
    }

    public function setIsSelling(int $is_selling): self
    {
        $this->is_selling = $is_selling;

        return $this;
    }

    public function getGuarantee(): ?string
    {
        return $this->guarantee;
    }

    public function setGuarantee(string $guarantee): self
    {
        $this->guarantee = $guarantee;

        return $this;
    }

    public function getIsPedigree(): ?int
    {
        return $this->is_pedigree;
    }

    public function setIsPedigree(int $is_pedigree): self
    {
        $this->is_pedigree = $is_pedigree;

        return $this;
    }

    public function getIncludeVaccineFee(): ?int
    {
        return $this->include_vaccine_fee;
    }

    public function setIncludeVaccineFee(int $include_vaccine_fee): self
    {
        $this->include_vaccine_fee = $include_vaccine_fee;

        return $this;
    }

    public function getDeliveryTime(): ?string
    {
        return $this->delivery_time;
    }

    public function setDeliveryTime(string $delivery_time): self
    {
        $this->delivery_time = $delivery_time;

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

    public function getReservationFee(): ?int
    {
        return $this->reservation_fee;
    }

    public function setReservationFee(int $reservation_fee): self
    {
        $this->reservation_fee = $reservation_fee;

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): self
    {
        $this->price = $price;

        return $this;
    }
}
