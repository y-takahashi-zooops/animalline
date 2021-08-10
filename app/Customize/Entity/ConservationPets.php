<?php

namespace Customize\Entity;

use Customize\Repository\ConservationPetsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ConservationPetsRepository::class)
 * @ORM\Table(name="alm_conservation_pets")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discriminator_type", type="string", length=255)
 * @ORM\HasLifecycleCallbacks()
 */
class ConservationPets
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(name="id", type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Conservations::class, inversedBy="conservationPets")
     * @ORM\JoinColumn(name="conservation_id", nullable=false)
     */
    private $conservation_id;

    /**
     * @ORM\Column(name="pet_kind", type="smallint")
     */
    private $pet_kind;

    /**
     * @ORM\ManyToOne(targetEntity="Customize\Entity\Breeds", inversedBy="conservationPets")
     * @ORM\JoinColumn(name="breeds_type", nullable=true)
     */
    private $breeds_type;

    /**
     * @ORM\Column(name="pet_sex", type="smallint")
     */
    private $pet_sex;

    /**
     * @ORM\Column(name="pet_birthday", type="date")
     */
    private $pet_birthday;

    /**
     * @ORM\ManyToOne(targetEntity="Customize\Entity\CoatColors", inversedBy="conservationPets")
     * @ORM\JoinColumn(name="coat_color", nullable=true)
     */
    private $coat_color;

    /**
     * @ORM\Column(name="future_wait", type="smallint")
     */
    private $future_wait;

    /**
     * @ORM\Column(name="dna_check_result", type="integer")
     */
    private $dna_check_result;

    /**
     * @ORM\Column(name="pr_comment", type="text")
     */
    private $pr_comment;

    /**
     * @ORM\Column(name="description", type="text")
     */
    private $description;

    /**
     * @ORM\Column(name="delivery_time", type="text")
     */
    private $delivery_time;

    /**
     * @ORM\Column(name="delivery_way", type="text")
     */
    private $delivery_way;

    /**
     * @ORM\Column(name="release_status", type="smallint", options={"default" = 0}, nullable=true)
     */
    private $release_status;

    /**
     * @ORM\Column(name="release_date", type="date", nullable=true)
     */
    private $release_date;

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
     * @ORM\Column(name="thumbnail_path", type="string", length=255, nullable=true)
     */
    private $thumbnail_path;

    /**
     * @ORM\Column(name="price", type="integer")
     */
    private $price;

    /**
     * @ORM\OneToMany(targetEntity=ConservationPetImage::class, mappedBy="conservation_pet_id", orphanRemoval=true)
     */
    private $conservationPetImages;

    public function __construct()
    {
        $this->conservationPetImages = new ArrayCollection();
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

    public function getBreedsType(): ?Breeds
    {
        return $this->breeds_type;
    }

    public function setBreedsType(Breeds $breeds_type): self
    {
        $this->breeds_type = $breeds_type;

        return $this;
    }

    public function getPetSex(): ?int
    {
        return $this->pet_sex;
    }

    public function setPetSex(int $pet_sex): self
    {
        $this->pet_sex = $pet_sex;

        return $this;
    }

    public function getPetBirthday(): ?\DateTimeInterface
    {
        return $this->pet_birthday;
    }

    public function setPetBirthday(\DateTimeInterface $pet_birthday): self
    {
        $this->pet_birthday = $pet_birthday;

        return $this;
    }

    public function getCoatColor(): ?CoatColors
    {
        return $this->coat_color;
    }

    public function setCoatColor(?CoatColors $coat_color): self
    {
        $this->coat_color = $coat_color;

        return $this;
    }

    public function getFutureWait(): ?int
    {
        return $this->future_wait;
    }

    public function setFutureWait(int $future_wait): self
    {
        $this->future_wait = $future_wait;

        return $this;
    }

    public function getDnaCheckResult(): ?int
    {
        return $this->dna_check_result;
    }

    public function setDnaCheckResult(int $dna_check_result): self
    {
        $this->dna_check_result = $dna_check_result;

        return $this;
    }

    public function getPrComment(): ?string
    {
        return $this->pr_comment;
    }

    public function setPrComment(string $pr_comment): self
    {
        $this->pr_comment = $pr_comment;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getDeliveryTime(): ?string
    {
        return $this->delivery_time;
    }

    public function setDeliveryTime(string $delivery_time): self
    {
        $this->delivery_time = $delivery_time;

        return $this;
    }

    public function getDeliveryWay(): ?string
    {
        return $this->delivery_way;
    }

    public function setDeliveryWay(string $delivery_way): self
    {
        $this->delivery_way = $delivery_way;

        return $this;
    }

    public function getThumbnailPath(): ?string
    {
        return $this->thumbnail_path;
    }

    public function setThumbnailPath(string $thumbnail_path): self
    {
        $this->thumbnail_path = $thumbnail_path;

        return $this;
    }

    public function getReleaseStatus(): ?int
    {
        return $this->release_status;
    }

    public function setReleaseStatus(int $release_status): self
    {
        $this->release_status = $release_status;

        return $this;
    }

    public function getReleaseDate(): ?\DateTimeInterface
    {
        return $this->release_date;
    }

    public function setReleaseDate(\DateTimeInterface $release_date): self
    {
        $this->release_date = $release_date;

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

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): self
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @return Collection|ConservationPetImage[]
     */
    public function getConservationPetImages(): Collection
    {
        return $this->conservationPetImages;
    }

    public function addConservationPetImage(ConservationPetImage $conservationPetImage): self
    {
        if (!$this->conservationPetImages->contains($conservationPetImage)) {
            $this->conservationPetImages[] = $conservationPetImage;
            $conservationPetImage->setConservationPetId($this);
        }

        return $this;
    }

    public function removeConservationPetImage(ConservationPetImage $conservationPetImage): self
    {
        if ($this->conservationPetImages->removeElement($conservationPetImage)) {
            // set the owning side to null (unless already changed)
            if ($conservationPetImage->getConservationPetId() === $this) {
                $conservationPetImage->setConservationPetId(null);
            }
        }

        return $this;
    }

    public function getConservationId(): ?Conservations
    {
        return $this->conservation_id;
    }

    public function setConservationId(?Conservations $conservation_id): self
    {
        $this->conservation_id = $conservation_id;

        return $this;
    }
}
