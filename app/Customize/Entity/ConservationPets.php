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
     * @ORM\ManyToOne(targetEntity=Conservations::class, inversedBy="ConservationPets")
     * @ORM\JoinColumn(name="conservation_id", nullable=false)
     */
    private $Conservation;

    /**
     * @ORM\Column(name="pet_kind", type="smallint")
     */
    private $pet_kind;

    /**
     * @ORM\ManyToOne(targetEntity="Customize\Entity\Breeds", inversedBy="ConservationPets")
     * @ORM\JoinColumn(name="breeds_type", nullable=true)
     */
    private $BreedsType;

    /**
     * @ORM\Column(name="name", type="string", length=64, nullable=true)
     */
    private $name;

    /**
     * @ORM\Column(name="pet_sex", type="smallint")
     */
    private $pet_sex;

    /**
     * @ORM\Column(name="pet_birthday", type="date")
     */
    private $pet_birthday;

    /**
     * @ORM\Column(name="pet_age", type="string", length=20, nullable=true)
     */
    private $pet_age;

    /**
     * @ORM\Column(name="coat_color", type="string", length=20, nullable=true)
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
     * @ORM\Column(name="delivery_time", type="text", nullable=true)
     */
    private $delivery_time;

    /**
     * @ORM\Column(name="delivery_way", type="text")
     */
    private $delivery_way;

    /**
     * @ORM\Column(name="is_active", type="smallint", options={"default" = 0}, nullable=true)
     */
    private $is_active;

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
     * @ORM\Column(name="is_vaccine", type="smallint", options={"default" = 0}, nullable=true)
     */
    private $is_vaccine;

    /**
     * @ORM\Column(name="is_castration", type="smallint", options={"default" = 0}, nullable=true)
     */
    private $is_castration;

    /**
     * @ORM\Column(name="is_single", type="smallint", options={"default" = 0}, nullable=true)
     */
    private $is_single;

    /**
     * @ORM\Column(name="is_senior", type="smallint", options={"default" = 0}, nullable=true)
     */
    private $is_senior;

    /**
     * @ORM\Column(name="thumbnail_path", type="string", length=255, nullable=true)
     */
    private $thumbnail_path;

    /**
     * @ORM\Column(name="price", type="integer", nullable=true)
     */
    private $price;

    /**
     * @ORM\Column(name="price_comment", type="string", length=255, nullable=true)
     */
    private $price_comment;

    /**
     * @ORM\Column(name="reason_comment", type="string", length=255, nullable=true)
     */
    private $reason_comment;

    /**
     * @ORM\OneToMany(targetEntity=ConservationPetImage::class, mappedBy="ConservationPet", orphanRemoval=true)
     */
    private $ConservationPetImages;

    /**
     * @ORM\Column(name="favorite_count", type="integer", options={"default" = 0}, nullable=true)
     */
    private $favorite_count = 0;

    /**
     * @ORM\Column(name="is_delete",type="smallint", nullable=true, options={"default" = 0})
     */
    private $is_delete = 0;

    /**
     * @ORM\Column(name="view_count", type="smallint", options={"default" = 0})
     */
    private $view_count;

    /**
     * @ORM\Column(name="movie_file", type="string", length=255, nullable=true)
     */
    private $movie_file;

    public function __construct()
    {
        $this->ConservationPetImages = new ArrayCollection();
    }

    public function getViewCount(): ?int
    {
        return $this->view_count;
    }

    public function setViewCount(?int $view_count): self
    {
        $this->view_count = $view_count;

        return $this;
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
        return $this->BreedsType;
    }

    public function setBreedsType(?Breeds $BreedsType): self
    {
        $this->BreedsType = $BreedsType;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setname(?string $name): self
    {
        $this->name = $name;

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

    public function getPetAge(): ?string
    {
        return $this->pet_age;
    }

    public function setPetAge(string $pet_age): self
    {
        $this->pet_age = $pet_age;

        return $this;
    }

    public function getCoatColor(): ?string
    {
        return $this->coat_color;
    }

    public function setCoatColor(?string $coat_color): self
    {
        $this->coat_color = $coat_color;

        return $this;
    }

    public function getFutureWait(): ?int
    {
        return $this->future_wait;
    }

    public function setFutureWait(?int $future_wait): self
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

    public function setPrComment(?string $pr_comment): self
    {
        $this->pr_comment = $pr_comment;

        return $this;
    }

    public function getPriceComment(): ?string
    {
        return $this->price_comment;
    }

    public function setPriceComment(?string $price_comment): self
    {
        $this->price_comment = $price_comment;

        return $this;
    }

    public function getReasonComment(): ?string
    {
        return $this->reason_comment;
    }

    public function setReasonComment(?string $reason_comment): self
    {
        $this->reason_comment = $reason_comment;

        return $this;
    }


    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getDeliveryTime(): ?string
    {
        return $this->delivery_time;
    }

    public function setDeliveryTime(?string $delivery_time): self
    {
        $this->delivery_time = $delivery_time;

        return $this;
    }

    public function getDeliveryWay(): ?string
    {
        return $this->delivery_way;
    }

    public function setDeliveryWay(?string $delivery_way): self
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
        $this->thumbnail_path = !empty($thumbnail_path) ? $thumbnail_path : null;

        return $this;
    }

    public function getIsSenior(): ?int
    {
        return $this->is_senior;
    }

    public function setIsSenior(int $is_senior): self
    {
        $this->is_senior = $is_senior;

        return $this;
    }

    public function getIsSingle(): ?int
    {
        return $this->is_single;
    }

    public function setIsSingle(int $is_single): self
    {
        $this->is_single = $is_single;

        return $this;
    }

    public function getIsCastration(): ?int
    {
        return $this->is_castration;
    }

    public function setIsCastration(int $is_castration): self
    {
        $this->is_castration = $is_castration;

        return $this;
    }

    public function getIsVaccine(): ?int
    {
        return $this->is_vaccine;
    }

    public function setIsVaccine(int $is_vaccine): self
    {
        $this->is_vaccine = $is_vaccine;

        return $this;
    }


    public function getIsActive(): ?int
    {
        return $this->is_active;
    }

    public function setIsActive(int $is_active): self
    {
        $this->is_active = $is_active;

        return $this;
    }

    public function getReleaseDate(): ?\DateTimeInterface
    {
        return $this->release_date;
    }

    public function setReleaseDate(?\DateTimeInterface $release_date): self
    {
        $this->release_date = $release_date;

        return $this;
    }

    public function getMovieFile(): ?string
    {
        return $this->movie_file;
    }

    public function setMovieFile(?string $movie_file): self
    {
        $this->movie_file = $movie_file;

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

    public function setPrice(?int $price): self
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @return Collection|ConservationPetImage[]
     */
    public function getConservationPetImages(): Collection
    {
        return $this->ConservationPetImages;
    }

    public function addConservationPetImage(ConservationPetImage $ConservationPetImage): self
    {
        if (!$this->ConservationPetImages->contains($ConservationPetImage)) {
            $this->ConservationPetImages[] = $ConservationPetImage;
            $ConservationPetImage->setConservationPet($this);
        }

        return $this;
    }

    public function removeConservationPetImage(ConservationPetImage $ConservationPetImage): self
    {
        if ($this->ConservationPetImages->removeElement($ConservationPetImage)) {
            // set the owning side to null (unless already changed)
            if ($ConservationPetImage->getConservationPet() === $this) {
                $ConservationPetImage->setConservationPet(null);
            }
        }

        return $this;
    }

    public function getConservation(): ?Conservations
    {
        return $this->Conservation;
    }

    public function setConservation(?Conservations $Conservation): self
    {
        $this->Conservation = $Conservation;

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

    public function getCreateDate()
    {
        return $this->create_date;
    }

    public function getUpdateDate()
    {
        return $this->update_date;
    }

    public function getIsDelete()
    {
        return $this->is_delete;
    }

    public function setIsDelete(?int $is_delete)
    {
        $this->is_delete = $is_delete;

        return $this;
    }
}
