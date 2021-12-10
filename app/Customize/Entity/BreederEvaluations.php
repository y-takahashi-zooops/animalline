<?php

namespace Customize\Entity;

use Customize\Repository\BreederEvaluationsRepository;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=BreederEvaluationsRepository::class)
 * @ORM\Table(name="ald_breeder_evaluations")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discriminator_type", type="string", length=255)
 * @ORM\HasLifecycleCallbacks()
 */
class BreederEvaluations
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(name="id", type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=BreederPets::class, inversedBy="BreederEvaluations")
     * @ORM\JoinColumn(name="pet_id", nullable=false)
     */
    private $Pet;


    /**
     * @ORM\Column(name="evaluation_value", type="integer", nullable=false)
     */
    private $evaluation_value;

    /**
     * @ORM\Column(name="evaluation_message", type="text", nullable=false)
     */
    private $evaluation_message;

    /**
     * @ORM\Column(name="image_path", type="string", length=255, nullable=true)
     */
    private $image_path;

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

    public function getPet(): ?BreederPets
    {
        return $this->Pet;
    }

    public function setPet(BreederPets $pet): self
    {
        $this->Pet = $pet;

        return $this;
    }

    public function getEvaluationValue(): ?int
    {
        return $this->evaluation_value;
    }

    public function setEvaluationValue(int $evaluation_value): self
    {
        $this->evaluation_value = $evaluation_value;

        return $this;
    }

    public function getEvaluationMessage(): ?string
    {
        return $this->evaluation_message;
    }

    public function setEvaluationMessage(string $evaluation_message): self
    {
        $this->evaluation_message = $evaluation_message;

        return $this;
    }

    public function getImagePath(): ?string
    {
        return $this->image_path;
    }

    public function setImagePath(?string $image_path): self
    {
        $this->image_path = $image_path;

        return $this;
    }

    /**
     * Set createDate.
     *
     * @param ?\DateTime $createDate
     *
     * @return self
     */
    public function setCreateDate(?\DateTime $createDate): self
    {
        $this->create_date = $createDate;

        return $this;
    }

    /**
     * Set updateDate.
     *
     * @param ?\DateTime $updateDate
     *
     * @return self
     */
    public function setUpdateDate(?\DateTime $updateDate): self
    {
        $this->update_date = $updateDate;

        return $this;
    }
}
