<?php

namespace Customize\Entity;

use Customize\Repository\PetsFavoriteRepository;
use Doctrine\ORM\Mapping as ORM;
use Eccube\Entity\Customer;

/**
 * @ORM\Table(name="ald_pets_favorite")
 * @ORM\Entity(repositoryClass=PetsFavoriteRepository::class)
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
     * @ORM\JoinColumn(nullable=false)
     */
    private $customer_id;

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
        return $this->customer_id;
    }

    public function setCustomerId(?Customer $customer_id): self
    {
        $this->customer_id = $customer_id;

        return $this;
    }
}
