<?php

namespace Customize\Entity;

use Customize\Repository\PedigreeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PedigreeRepository::class)
 * @ORM\Table(name="alm_pedigree")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discriminator_type", type="string", length=255)
 * @ORM\HasLifecycleCallbacks()
 */
class Pedigree
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id", type="integer")
     */
    private $id;

    /**
     * @ORM\Column(name="pet_kind", type="smallint", nullable = false)
     */
    private $pet_kind;

    /**
     * @ORM\Column(name="pedigree_name", type="string", length=30, nullable = true)
     */
    private $pedigree_name;

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
     * @ORM\OneToMany(targetEntity=BreederPets::class, mappedBy="Pedigree")
     */
    private $BreederPets;

    public function __construct()
    {
        $this->BreederPets = new ArrayCollection();
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

    public function getPedigreeName(): ?string
    {
        return $this->pedigree_name;
    }

    public function setPedigreeName(string $pedigree_name): self
    {
        $this->pedigree_name = $pedigree_name;

        return $this;
    }

    /**
     * @return Collection|BreederPets[]
     */
    public function getBreederPets(): Collection
    {
        return $this->BreederPets;
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
