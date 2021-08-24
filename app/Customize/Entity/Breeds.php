<?php

namespace Customize\Entity;

use Customize\Repository\BreedsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
     * @ORM\OneToMany(targetEntity="Customize\Entity\ConservationPets", mappedBy="breeds_type")
     */
    private $conservationPets;

    /**
     * @ORM\OneToMany(targetEntity="Customize\Entity\BreederPets", mappedBy="breeds_type")
     */
    private $breederPets;

    public function __construct()
    {
        $this->conservationPets = new ArrayCollection();
        $this->breederPets = new ArrayCollection();
    }

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

    /**
     * @return Collection|ConservationPets[]
     */
    public function getConservationPets(): Collection
    {
        return $this->conservationPets;
    }

    public function addConservationPet(ConservationPets $conservationPet): self
    {
        if (!$this->conservationPets->contains($conservationPet)) {
            $this->conservationPets[] = $conservationPet;
            $conservationPet->setBreedsType($this);
        }

        return $this;
    }

    public function removeConservationPet(ConservationPets $conservationPet): self
    {
        if ($this->conservationPets->removeElement($conservationPet)) {
            // set the owning side to null (unless already changed)
            if ($conservationPet->getBreedsType() === $this) {
                $conservationPet->setBreedsType(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|BreederPets[]
     */
    public function getBreederPets(): Collection
    {
        return $this->breederPets;
    }

    public function addBreederPet(BreederPets $breederPet): self
    {
        if (!$this->breederPets->contains($breederPet)) {
            $this->breederPets[] = $breederPet;
            $breederPet->setBreedsType($this);
        }

        return $this;
    }

    public function removeBreederPet(BreederPets $breederPet): self
    {
        if ($this->breederPets->removeElement($breederPet)) {
            // set the owning side to null (unless already changed)
            if ($breederPet->getBreedsType() === $this) {
                $breederPet->setBreedsType(null);
            }
        }

        return $this;
    }
}
