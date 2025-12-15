<?php

namespace Customize\Entity;

use Customize\Repository\BreederPetImageRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=BreederPetImageRepository::class)
 * @ORM\Table(name="alm_breeder_pet_image")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discriminator_type", type="string", length=255)
 * @ORM\HasLifecycleCallbacks()
 */
class BreederPetImage
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(name="id", type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=BreederPets::class, inversedBy="BreederPetImages")
     * @ORM\JoinColumn(name="breeder_pet_id", nullable=false)
     */
    private $BreederPets;

    /**
     * @ORM\Column(name="image_type", type="smallint")
     */
    private $image_type;

    /**
     * @ORM\Column(name="image_uri", type="string", length=255)
     */
    private $image_uri;

    /**
     * @ORM\Column(name="sort_order", type="smallint")
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

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBreederPet(): ?BreederPets
    {
        return $this->BreederPets;
    }

    public function setBreederPet(BreederPets $breeder_pet_id): self
    {
        $this->BreederPets = $breeder_pet_id;

        return $this;
    }

    public function getImageType(): ?int
    {
        return $this->image_type;
    }

    public function setImageType(int $image_type): self
    {
        $this->image_type = $image_type;

        return $this;
    }

    public function getImageUri(): ?string
    {
        return $this->image_uri;
    }

    public function setImageUri(string $image_uri): self
    {
        $this->image_uri = $image_uri;

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
     * @param ?\DateTime $createDate
     *
     * @return BreederPetImage
     */
    public function setCreateDate(?\Datetime $createDate): self
    {
        $this->create_date = $createDate;

        return $this;
    }

    /**
     * Set updateDate.
     *
     * @param ?\DateTime $updateDate
     *
     * @return BreederPetImage
     */
    public function setUpdateDate(?\Datetime $updateDate): self
    {
        $this->update_date = $updateDate;

        return $this;
    }
}
