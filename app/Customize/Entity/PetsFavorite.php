<?php

namespace Customize\Entity;

use Customize\Repository\PetsFavoriteRepository;
use Doctrine\ORM\Mapping as ORM;
use Eccube\Entity\Customer;

/**
 * @ORM\Entity(repositoryClass=PetsFavoriteRepository::class)
 * @ORM\Table(name="ald_pets_favorite")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discriminator_type", type="string", length=255)
 * @ORM\HasLifecycleCallbacks()
 */
class PetsFavorite
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="smallint", nullable=false)
     */
    private $site_category;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private $pet_id;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private $pet_kind;

    /**
     * @ORM\ManyToOne(targetEntity=Customer::class)
     * @ORM\JoinColumn(name="customer_id", nullable=false)
     */
    private $Customer;

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

    public function getSiteCategory(): ?int
    {
        return $this->site_category;
    }

    public function setSiteCategory(?int $site_category): self
    {
        $this->site_category = $site_category;

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

    public function getPetKind(): ?int
    {
        return $this->pet_kind;
    }

    public function setPetKind(?int $pet_kind): self
    {
        $this->pet_kind = $pet_kind;

        return $this;
    }

    public function getCustomerId(): ?Customer
    {
        return $this->Customer;
    }

    public function setCustomerId(?Customer $Customer): self
    {
        $this->Customer = $Customer;

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
