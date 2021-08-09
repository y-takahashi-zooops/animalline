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
     * @ORM\Column(name="conservation_id", type="integer")
     */
    private $conservation_id;

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

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getConservationId(): ?int
    {
        return $this->conservation_id;
    }

    public function setConservationId(int $conservation_id): self
    {
        $this->conservation_id = $conservation_id;

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
}
