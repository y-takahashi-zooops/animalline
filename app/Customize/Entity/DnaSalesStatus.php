<?php

namespace Customize\Entity;

use Customize\Repository\DnaSalesHeaderRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="ald_dna_sales_status")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\Entity(repositoryClass=DnaSalesStatusRepository::class)
 * @ORM\DiscriminatorColumn(name="discriminator_type", type="string", length=255)
 * @ORM\HasLifecycleCallbacks()
 */
class DnaSalesStatus
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=DnaSalesHeader::class, inversedBy="DnaSalesStatus")
     * @ORM\JoinColumn(name="header_id", nullable=false)
     */
    private $DnaSalesHeader;

    /**
     * @ORM\Column(type="integer")
     */
    private $pet_kind;

    /**
     * @ORM\ManyToOne(targetEntity="Customize\Entity\Breeds", inversedBy="DnaSalesStatus")
     * @ORM\JoinColumn(name="breeds_type", nullable=true)
     */
    private $BreedsType;

    /**
     * @ORM\Column(type="smallint", options={"default" : 0})
     */
    private $check_status;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $status_comment;

    /**
     * @ORM\Column(type="integer", options={"default" : 0})
     */
    private $price;

    /**
     * @ORM\Column(type="integer", options={"default" : 0})
     */
    private $test_count;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $file_path;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $kit_return_date;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $check_return_date;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $birthday;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $image_path;

    /**
     * @ORM\Column(type="string", length=40, nullable=true)
     */
    private $pet_name;

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
     * @var \Doctrine\Common\Collections\Collection|DnaDetails[]
     *
     * @ORM\OneToMany(targetEntity="Customize\Entity\DnaSalesDetail", mappedBy="DnaSalesStatus", cascade={"persist","remove"})
     */
    private $DnaDetails;

    public function getDetails()
    {
        return $this->DnaDetails;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDnaSalesHeader(): ?DnaSalesHeader
    {
        return $this->DnaSalesHeader;
    }

    public function setDnaSalesHeader(?DnaSalesHeader $DnaSalesHeader): self
    {
        $this->DnaSalesHeader = $DnaSalesHeader;

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

    public function getBreedsType(): ?Breeds
    {
        return $this->BreedsType;
    }

    public function setBreedsType(?Breeds $BreedsType): self
    {
        $this->BreedsType = $BreedsType;

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

    public function setTestCount(int $test_count): self
    {
        $this->test_count = $test_count;

        return $this;
    }

    public function getTestCount(): ?int
    {
        return $this->test_count;
    }

    public function getCheckStatus(): ?int
    {
        return $this->check_status;
    }

    public function setCheckStatus(int $check_status): self
    {
        $this->check_status = $check_status;

        return $this;
    }

    public function getStatusComment(): ?string
    {
        return $this->status_comment;
    }

    public function setStatusComment(?string $status_comment): self
    {
        $this->status_comment = $status_comment;

        return $this;
    }

    public function getFilePath(): ?string
    {
        return $this->file_path;
    }

    public function setFilePath(?string $file_path): self
    {
        $this->file_path = $file_path;

        return $this;
    }

    public function getKitReturnDate(): ?\DateTimeInterface
    {
        return $this->kit_return_date;
    }

    public function setKitReturnDate(?\DateTimeInterface $kit_return_date): self
    {
        $this->kit_return_date = $kit_return_date;

        return $this;
    }

    public function getCheckReturnDate(): ?\DateTimeInterface
    {
        return $this->check_return_date;
    }

    public function setCheckReturnDate(?\DateTimeInterface $check_return_date): self
    {
        $this->check_return_date = $check_return_date;

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

    public function getImagePath()
    {
        return $this->image_path;
    }

    public function setImagePath($image_path)
    {
        $this->image_path = $image_path;

        return $this;
    }

    public function getPetName()
    {
        return $this->pet_name;
    }

    public function setPetName($pet_name)
    {
        $this->pet_name = $pet_name;

        return $this;
    }

    public function getBirthDay()
    {
        return $this->birthday;
    }

    public function setBirthDay($birthday)
    {
        $this->birthday = $birthday;

        return $this;
    }
}
