<?php

namespace Customize\Entity;

use Customize\Repository\ConservationPetImageRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ConservationPetImageRepository::class)
 * @ORM\Table(name="alm_conservation_pet_image")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discriminator_type", type="string", length=255)
 * @ORM\HasLifecycleCallbacks()
 */
class ConservationPetImage
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(name="id", type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=ConservationPets::class, inversedBy="ConservationPetImages")
     * @ORM\JoinColumn(name="conservation_pet_id", nullable=false)
     */
    private $ConservationPet;

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

    public function getConservationPet(): ?ConservationPets
    {
        return $this->ConservationPet;
    }

    public function setConservationPet(?ConservationPets $ConservationPet): self
    {
        $this->ConservationPet = $ConservationPet;

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
