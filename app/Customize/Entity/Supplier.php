<?php

namespace Customize\Entity;

use Customize\Repository\SupplierRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Table(name="alm_supplier")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discriminator_type", type="string", length=255)
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Entity(repositoryClass=SupplierRepository::class)
 * @UniqueEntity(fields={"supplier_code"}, message="仕入先コードが既に存在しました。")
 */
class Supplier
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(name="id", type="integer")
     */
    private $id;

    /**
     * @ORM\Column(name="supplier_code", type="string", unique=true, length=5, nullable=false)
     */
    private $supplier_code;

    /**
     * @ORM\Column(name="supplier_name", type="string", length=20, nullable=false)
     */
    private $supplier_name;

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
