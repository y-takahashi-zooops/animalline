<?php

namespace Customize\Entity;

use Customize\Repository\BreederPetsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=BreederPetsRepository::class)
 * @ORM\Table(name="alm_breeder_pets")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discriminator_type", type="string", length=255)
 * @ORM\HasLifecycleCallbacks()
 */
class BreederPets
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(name="id", type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Breeders::class, inversedBy="breederPets")
     * @ORM\JoinColumn(name="breeder_id", nullable=false)
     */
    private $breeder;

    /**
     * @ORM\Column(name="pet_kind", type="smallint")
     */
    private $pet_kind;

    /**
     * @ORM\ManyToOne(targetEntity="Customize\Entity\Breeds", inversedBy="breederPets")
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
     * @ORM\Column(name="is_breeding", type="smallint")
     */
    private $is_breeding;

    /**
     * @ORM\Column(name="is_selling", type="smallint")
     */
    private $is_selling;

    /**
     * @ORM\Column(name="guarantee", type="text")
     */
    private $guarantee;

    /**
     * @ORM\Column(name="is_pedigree", type="smallint")
     */
    private $is_pedigree;

    /**
     * @ORM\Column(name="include_vaccine_fee", type="smallint")
     */
    private $include_vaccine_fee;

    /**
     * @ORM\Column(name="delivery_time", type="text")
     */
    private $delivery_time;

    /**
     * @ORM\Column(name="delivery_way", type="text")
     */
    private $delivery_way;

    /**
     * @ORM\Column(name="payment_method", type="text")
     */
    private $payment_method;

    /**
     * @ORM\Column(name="reservation_fee", type="integer")
     */
    private $reservation_fee;

    /**
     * @ORM\Column(name="price", type="integer")
     */
    private $price;

    /**
     * @ORM\Column(name="thumbnail_path", type="string", length=255, nullable=true)
     */
    private $thumbnail_path;

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
     * @ORM\OneToMany(targetEntity=BreederPetImage::class, mappedBy="breeder_pet_id", orphanRemoval=true)
     */
    private $breederPetImages;

    /**
     * @ORM\OneToMany(targetEntity=BreederContacts::class, mappedBy="pet_id")
     */
    private $breederContacts;

    /**
     * @ORM\Column(name="favorite_count", type="integer", options={"default" = 0}, nullable=true)
     */
    private $favorite_count = 0;

    /**
     * @ORM\OneToMany(targetEntity=PetsFavorite::class, mappedBy="pet_id")
     */
    private $petsFavorites;

    public function __construct()
    {
        $this->breederPetImages = new ArrayCollection();
        $this->breederContacts = new ArrayCollection();
        $this->petsFavorites = new ArrayCollection();
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

    public function getIsBreeding(): ?int
    {
        return $this->is_breeding;
    }

    public function setIsBreeding(int $is_breeding): self
    {
        $this->is_breeding = $is_breeding;

        return $this;
    }

    public function getIsSelling(): ?int
    {
        return $this->is_selling;
    }

    public function setIsSelling(int $is_selling): self
    {
        $this->is_selling = $is_selling;

        return $this;
    }

    public function getGuarantee(): ?string
    {
        return $this->guarantee;
    }

    public function setGuarantee(string $guarantee): self
    {
        $this->guarantee = $guarantee;

        return $this;
    }

    public function getIsPedigree(): ?int
    {
        return $this->is_pedigree;
    }

    public function setIsPedigree(int $is_pedigree): self
    {
        $this->is_pedigree = $is_pedigree;

        return $this;
    }

    public function getIncludeVaccineFee(): ?int
    {
        return $this->include_vaccine_fee;
    }

    public function setIncludeVaccineFee(int $include_vaccine_fee): self
    {
        $this->include_vaccine_fee = $include_vaccine_fee;

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

    public function getPaymentMethod(): ?string
    {
        return $this->payment_method;
    }

    public function setPaymentMethod(string $payment_method): self
    {
        $this->payment_method = $payment_method;

        return $this;
    }

    public function getReservationFee(): ?int
    {
        return $this->reservation_fee;
    }

    public function setReservationFee(int $reservation_fee): self
    {
        $this->reservation_fee = $reservation_fee;

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

    /**
     * @return Collection|BreederPetImage[]
     */
    public function getBreederPetImages(): Collection
    {
        return $this->breederPetImages;
    }

    public function addBreederPetImage(BreederPetImage $breederPetImage): self
    {
        if (!$this->breederPetImages->contains($breederPetImage)) {
            $this->breederPetImages[] = $breederPetImage;
            $breederPetImage->setBreederPetId($this);
        }

        return $this;
    }

    public function removeBreederPetImage(BreederPetImage $breederPetImage): self
    {
        if ($this->breederPetImages->removeElement($breederPetImage)) {
            // set the owning side to null (unless already changed)
            if ($breederPetImage->getBreederPetId() === $this) {
                $breederPetImage->setBreederPetId(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|BreederContacts[]
     */
    public function getBreederContacts(): Collection
    {
        return $this->breederContacts;
    }

    public function addBreederContact(BreederContacts $breederContact): self
    {
        if (!$this->breederContacts->contains($breederContact)) {
            $this->breederContacts[] = $breederContact;
            $breederContact->setPet($this);
        }

        return $this;
    }

    public function removeBreederContact(BreederContacts $breederContact): self
    {
        if ($this->breederContacts->removeElement($breederContact)) {
            // set the owning side to null (unless already changed)
            if ($breederContact->getPet() === $this) {
                $breederContact->setPet(null);
            }
        }

        return $this;
    }

    public function getFavoriteCount(): ?int
    {
        return $this->favorite_count;
    }

    public function setFavoriteCount(int $favorite_count): self
    {
        $this->favorite_count = $favorite_count;

        return $this;
    }

    /**
     * @return Collection|PetsFavorite[]
     */
    public function getPetsFavorites(): Collection
    {
        return $this->petsFavorites;
    }

    public function addPetsFavorite(PetsFavorite $petsFavorite): self
    {
        if (!$this->petsFavorites->contains($petsFavorite)) {
            $this->petsFavorites[] = $petsFavorite;
            $petsFavorite->setPetId($this);
        }

        return $this;
    }

    public function removePetsFavorite(PetsFavorite $petsFavorite): self
    {
        if ($this->petsFavorites->removeElement($petsFavorite)) {
            // set the owning side to null (unless already changed)
            if ($petsFavorite->getPetId() === $this) {
                $petsFavorite->setPetId(null);
            }
        }

        return $this;
    }
}
