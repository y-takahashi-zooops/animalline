<?php

namespace Customize\Entity;

use Customize\Repository\SupplierRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="alm_supplier")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discriminator_type", type="string", length=255)
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Entity(repositoryClass=SupplierRepository::class)
 */
class Supplier
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=5, nullable="false")
     */
    private $supplier_code;

    /**
     * @ORM\Column(type="string", length=20, nullable="false")
     */
    private $supplier_name;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $create_date;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $update_date;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $discriminator_type;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSupplierCode(): ?string
    {
        return $this->supplier_code;
    }

    public function setSupplierCode(string $supplier_code): self
    {
        $this->supplier_code = $supplier_code;

        return $this;
    }

    public function getSupplierName(): ?string
    {
        return $this->supplier_name;
    }

    public function setSupplierName(string $supplier_name): self
    {
        $this->supplier_name = $supplier_name;

        return $this;
    }

    public function getCreateDate(): ?\DateTimeInterface
    {
        return $this->create_date;
    }

    public function setCreateDate(?\DateTimeInterface $create_date): self
    {
        $this->create_date = $create_date;

        return $this;
    }

    public function getUpdateDate(): ?\DateTimeInterface
    {
        return $this->update_date;
    }

    public function setUpdateDate(?\DateTimeInterface $update_date): self
    {
        $this->update_date = $update_date;

        return $this;
    }

    public function getDiscriminatorType(): ?string
    {
        return $this->discriminator_type;
    }

    public function setDiscriminatorType(?string $discriminator_type): self
    {
        $this->discriminator_type = $discriminator_type;

        return $this;
    }
}
