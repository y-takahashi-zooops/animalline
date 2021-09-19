<?php

namespace Customize\Entity;

use Customize\Repository\DnaCheckStatusRepository;
use Doctrine\ORM\Mapping as ORM;
use Eccube\Entity\Customer;

/**
 * @ORM\Table(name="ald_dna_check_status")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discriminator_type", type="string", length=255)
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Entity(repositoryClass=DnaCheckStatusRepository::class)
 */
class DnaCheckStatus
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
     * @ORM\Column(name="pet_id", type="integer")
     */
    private $pet_id;

    /**
     * @ORM\Column(name="site_type", type="smallint")
     */
    private $site_type;

    /**
     * @ORM\Column(name="check_status", type="smallint", options={"default" = 1})
     */
    private $check_status = 1;

    /**
     * @ORM\Column(name="status_comment", type="string", length=255, nullable=true)
     */
    private $status_comment;

    /**
     * @ORM\Column(name="file_path", type="string", length=255, nullable=true)
     */
    private $file_path;

    /**
     * @ORM\Column(name="kit_shipping_date", type="datetime", nullable=true)
     */
    private $kit_shipping_date;

    /**
     * @ORM\Column(name="kit_return_date", type="datetime", nullable=true)
     */
    private $kit_return_date;

    /**
     * @ORM\Column(name="check_return_date", type="datetime", nullable=true)
     */
    private $check_return_date;

    /**
     * @ORM\Column(name="create_date", type="datetime", nullable=true)
     */
    private $create_date;

    /**
     * @ORM\Column(name="update_date", type="datetime", nullable=true)
     */
    private $update_date;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRegisterId(): int
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

    public function getKitShippingDate(): ?\DateTime
    {
        return $this->kit_shipping_date;
    }

    public function setKitShippingDate(?\DateTime $kit_shipping_date): self
    {
        $this->kit_shipping_date = $kit_shipping_date;

        return $this;
    }

    public function getKitReturnDate(): ?\DateTime
    {
        return $this->kit_return_date;
    }

    public function setKitReturnDate(?\DateTime $kit_return_date): self
    {
        $this->kit_return_date = $kit_return_date;

        return $this;
    }

    public function getCheckReturnDate(): ?\DateTime
    {
        return $this->check_return_date;
    }

    public function setCheckReturnDate(?\DateTime $check_return_date): self
    {
        $this->check_return_date = $check_return_date;

        return $this;
    }

    public function getCreateDate(): ?\DateTime
    {
        return $this->create_date;
    }

    public function setCreateDate(\DateTime $create_date): self
    {
        $this->create_date = $create_date;

        return $this;
    }

    public function getUpdateDate(): ?\DateTime
    {
        return $this->update_date;
    }

    public function setUpdateDate(\DateTime $update_date): self
    {
        $this->update_date = $update_date;

        return $this;
    }
}
