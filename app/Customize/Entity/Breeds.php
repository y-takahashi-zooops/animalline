<?php

namespace Customize\Entity;

use Customize\Repository\BreedsRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="alm_breeds")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discriminator_type", type="string", length=255)
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Entity(repositoryClass=BreedsRepository::class)
 */
class Breeds
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(name="id", type="integer")
     */
    private $id;

    /**
     * @ORM\Column(name="pet_kind", type="smallint")
     */
    private $pet_kind;

    /**
     * @ORM\Column(name="breeds_name", type="string", length=255)
     */
    private $breeds_name;

    /**
     * @ORM\Column(name="sort_order", type="integer")
     */
    private $sort_order;

    private $discriminator_type;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getBreedsName(): ?string
    {
        return $this->breeds_name;
    }

    public function setBreedsName(string $breeds_name): self
    {
        $this->breeds_name = $breeds_name;

        return $this;
    }

    public function getSortOrder(): ?int
    {
        return $this->sort_order;
    }

    public function setSortOrder(int $sort_order): self
    {
        $this->sort_order = $sort_order;

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
