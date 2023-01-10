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
     * @ORM\Column(type="integer")
     */
    private $header_id;

    /**
     * @ORM\Column(type="integer")
     */
    private $pet_kind;

    /**
     * @ORM\Column(type="integer")
     */
    private $breeds_type;

    /**
     * @ORM\Column(type="smallint", options={"default" : 0})
     */
    private $check_status;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $status_comment;

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

    public function getHeaderId(): ?int
    {
        return $this->header_id;
    }

    public function setHeaderId(int $header_id): self
    {
        $this->header_id = $header_id;

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
}
