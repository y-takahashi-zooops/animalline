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
     * @ORM\Column(name="pet_kind", type="smallint", nullable=true)
     */
    private $pet_kind;

    /**
     * @ORM\ManyToOne(targetEntity=Breeders::class, inversedBy="BreederPets")
     * @ORM\JoinColumn(name="breeder_id", nullable=true)
     */
    private $Breeder;

    /**
     * @ORM\ManyToOne(targetEntity="Customize\Entity\Breeds", inversedBy="BreederPets")
     * @ORM\JoinColumn(name="breeds_type", nullable=true)
     */
    private $BreedsType;

    /**
     * @ORM\Column(name="pet_sex", type="smallint", nullable=true)
     */
    private $pet_sex;

    /**
     * @ORM\Column(name="pet_birthday", type="date", nullable=true)
     */
    private $pet_birthday;

    /**
     * @ORM\Column(name="coat_color", type="string", length=20, nullable=true)
     */
    private $coat_color;

    /**
     * @ORM\Column(name="future_wait", type="integer", nullable=true)
     */
    private $future_wait;

    /**
     * @ORM\Column(name="dna_check_result", type="integer", options={"default" = 0}, nullable=true)
     */
    private $dna_check_result;

    /**
     * @ORM\Column(name="pr_comment", type="text", nullable=true)
     */
    private $pr_comment;

    /**
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(name="is_breeding", type="smallint", nullable=true)
     */
    private $is_breeding;

    /**
     * @ORM\Column(name="is_selling", type="smallint", nullable=true)
     */
    private $is_selling;

    /**
     * @ORM\Column(name="guarantee", type="text", nullable=true)
     */
    private $guarantee;

    /**
     * @ORM\Column(name="is_pedigree", type="smallint", nullable=true)
     */
    private $is_pedigree;

    /**
     * @ORM\Column(name="include_vaccine_fee", type="smallint", nullable=true)
     */
    private $include_vaccine_fee;

    /**
     * @ORM\Column(name="vaccine_detail", type="string", length=128, nullable=true)
     */
    private $vaccine_detail;

    /**
     * @ORM\Column(name="pedigree_detail", type="string", length=128, nullable=true)
     */
    private $pedigree_detail;

    /**
     * @ORM\Column(name="delivery_way", type="text", nullable=true)
     */
    private $delivery_way;

    /**
     * @ORM\Column(name="price", type="integer", nullable=true)
     */
    private $price;

    /**
     * @ORM\Column(name="price_no_display", type="smallint", options={"default" = 0}, nullable=false)
     */
    private $price_no_display;

    /**
     * @ORM\Column(name="is_contact", type="smallint", options={"default" = 0}, nullable=false)
     */
    private $is_contact;

    /**
     * @ORM\Column(name="thumbnail_path", type="string", length=255, nullable=true)
     */
    private $thumbnail_path;

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
    private $BreederPetImages;

    /**
     * @ORM\Column(name="favorite_count", type="integer", options={"default" = 0}, nullable=true)
     */
    private $favorite_count = 0;

    /**
     * @ORM\OneToMany(targetEntity=PetsFavorite::class, mappedBy="pet_id")
     */
    private $PetsFavorites;

    /**
     * @ORM\OneToMany(targetEntity=BreederContactHeader::class, mappedBy="Pet")
     */
    private $BreederContactHeader;

    /**
     * @ORM\OneToMany(targetEntity=BreederEvaluations::class, mappedBy="Pet")
     */
    private $BreederEvaluations;

    /**
     * @ORM\ManyToOne(targetEntity=Pedigree::class)
     * @ORM\JoinColumn(name="pedigree_id", nullable=true)
     */
    private $Pedigree;

    /**
     * @ORM\Column(name="pedigree_code", type="string", length=20, nullable=true)
     */
    private $pedigree_code;

    /**
     * @ORM\Column(name="microchip_code", type="string", length=20, nullable=true)
     */
    private $microchip_code;

    /**
     * @ORM\Column(name="is_microchip", type="smallint", options={"default" = 0})
     */
    private $is_microchip = 0;

    /**
     * @ORM\Column(name="is_active", type="smallint", options={"default" = 0})
     */
    private $is_active = 0;

    /**
     * @ORM\Column(name="release_date", type="date", nullable=true)
     */
    private $release_date;

    /**
     * @ORM\Column(name="pet_code", type="string", length=10, nullable=true)
     */
    private $pet_code;

    /**
     * @ORM\Column(name="band_color", type="smallint", nullable=true)
     */
    private $band_color;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $payment_method;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $reservation_fee;

    /**
     * @ORM\Column(name="is_delete",type="smallint", nullable=true, options={"default" = 0})
     */
    private $is_delete = 0;
    
    /**
     * @ORM\Column(name="view_count", type="smallint", options={"default" = 0})
     */
    private $view_count;

    /**
     * @ORM\Column(name="is_contract",type="smallint", nullable=true, options={"default" = 0})
     */
    private $is_contract = 0;

    /**
     * @ORM\Column(name="movie_file", type="string", length=255, nullable=true)
     */
    private $movie_file;

    public function __construct()
    {
        $this->BreederPetImages = new ArrayCollection();
        $this->PetsFavorites = new ArrayCollection();
        $this->BreederContactHeader = new ArrayCollection();
        $this->BreederEvaluations = new ArrayCollection();
    }

    public function getIsContract(): ?int
    {
        return $this->is_contract;
    }

    public function setIsContract(?int $is_contract): self
    {
        $this->is_contract = $is_contract;

        return $this;
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

    public function setPetKind(?int $pet_kind): self
    {
        $this->pet_kind = $pet_kind;

        return $this;
    }

    public function getBreedsType(): ?Breeds
    {
        return $this->BreedsType;
    }

    public function setBreedsType(?Breeds $breeds_type): self
    {
        $this->BreedsType = $breeds_type;

        return $this;
    }

    public function getPetSex(): ?int
    {
        return $this->pet_sex;
    }

    public function setPetSex(?int $pet_sex): self
    {
        $this->pet_sex = $pet_sex;

        return $this;
    }

    public function getPetBirthday(): ?\DateTimeInterface
    {
        return $this->pet_birthday;
    }

    public function setPetBirthday(?\DateTimeInterface $pet_birthday): self
    {
        $this->pet_birthday = $pet_birthday;

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

    public function setDnaCheckResult(?int $dna_check_result): self
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getIsBreeding(): ?int
    {
        return $this->is_breeding;
    }

    public function setIsBreeding(?int $is_breeding): self
    {
        $this->is_breeding = $is_breeding;

        return $this;
    }

    public function getIsSelling(): ?int
    {
        return $this->is_selling;
    }

    public function setIsSelling(?int $is_selling): self
    {
        $this->is_selling = $is_selling;

        return $this;
    }

    public function getGuarantee(): ?string
    {
        return $this->guarantee;
    }

    public function setGuarantee(?string $guarantee): self
    {
        $this->guarantee = $guarantee;

        return $this;
    }

    public function getIsPedigree(): ?int
    {
        return $this->is_pedigree;
    }

    public function setIsPedigree(?int $is_pedigree): self
    {
        $this->is_pedigree = $is_pedigree;

        return $this;
    }

    public function getIncludeVaccineFee(): ?int
    {
        return $this->include_vaccine_fee;
    }

    public function setIncludeVaccineFee(?int $include_vaccine_fee): self
    {
        $this->include_vaccine_fee = $include_vaccine_fee;

        return $this;
    }

    public function getVaccineDetail(): ?string
    {
        return $this->vaccine_detail;
    }

    public function setVaccineDetail(?string $vaccine_detail): self
    {
        $this->vaccine_detail = $vaccine_detail;

        return $this;
    }

    public function getPedigreeDetail(): ?string
    {
        return $this->pedigree_detail;
    }

    public function setPedigreeDetail(?string $pedigree_detail): self
    {
        $this->pedigree_detail = $pedigree_detail;

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

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(?int $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getPriceNoDisplay(): ?int
    {
        return $this->price_no_display;
    }

    public function setPriceNoDisplay(?int $price_no_display): self
    {
        $this->price_no_display = $price_no_display;

        return $this;
    }

    public function getIsContact(): ?int
    {
        return $this->is_contact;
    }

    public function setIsContact(?int $is_contact): self
    {
        $this->is_contact = $is_contact;

        return $this;
    }

    public function getThumbnailPath(): ?string
    {
        return $this->thumbnail_path;
    }

    public function setThumbnailPath(?string $thumbnail_path): self
    {
        $this->thumbnail_path = $thumbnail_path;

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

    /**
     * @return Collection|BreederPetImage[]
     */
    public function getBreederPetImages(): Collection
    {
        return $this->BreederPetImages;
    }

    public function addBreederPetImage(BreederPetImage $breederPetImage): self
    {
        if (!$this->BreederPetImages->contains($breederPetImage)) {
            $this->BreederPetImages[] = $breederPetImage;
            $breederPetImage->setBreederPet($this);
        }

        return $this;
    }

    public function removeBreederPetImage(BreederPetImage $breederPetImage): self
    {
        if ($this->BreederPetImages->removeElement($breederPetImage)) {
            // set the owning side to null (unless already changed)
            if ($breederPetImage->getBreederPet() === $this) {
                $breederPetImage->setBreederPet(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|BreederContactHeader[]
     */
    public function getBreederContactHeader(): Collection
    {
        return $this->BreederContactHeader;
    }

    public function addBreederContactHeader(BreederContactHeader $breederContactHeader): self
    {
        if (!$this->BreederContactHeader->contains($breederContactHeader)) {
            $this->BreederContactHeader[] = $breederContactHeader;
            $breederContactHeader->setPet($this);
        }

        return $this;
    }

    /**
     * @return Collection|BreederEvaluations[]
     */
    public function getBreederEvaluations(): Collection
    {
        return $this->BreederEvaluations;
    }

    public function getFavoriteCount(): ?int
    {
        return $this->favorite_count;
    }

    public function setFavoriteCount(?int $favorite_count): self
    {
        $this->favorite_count = $favorite_count;
        return $this;
    }

    public function removeBreederContactHeader(BreederContactHeader $breederContactHeader): self
    {
        if ($this->BreederContactHeader->removeElement($breederContactHeader)) {
            // set the owning side to null (unless already changed)
            if ($breederContactHeader->getPet() === $this) {
                $breederContactHeader->setPet(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|PetsFavorite[]
     */
    public function getPetsFavorites(): Collection
    {
        return $this->PetsFavorites;
    }

    public function addPetsFavorite(PetsFavorite $petsFavorite): self
    {
        if (!$this->PetsFavorites->contains($petsFavorite)) {
            $this->PetsFavorites[] = $petsFavorite;
            $petsFavorite->setPetId($this->getId());
        }

        return $this;
    }

    public function removePetsFavorite(PetsFavorite $petsFavorite): self
    {
        if ($this->PetsFavorites->removeElement($petsFavorite)) {
            // set the owning side to null (unless already changed)
            if ($petsFavorite->getPetId() === $this) {
                $petsFavorite->setPetId(null);
            }
        }

        return $this;
    }

    public function getBreeder(): ?Breeders
    {
        return $this->Breeder;
    }

    public function setBreeder(?Breeders $Breeder): self
    {
        $this->Breeder = $Breeder;

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

    public function getPedigree(): ?Pedigree
    {
        return $this->Pedigree;
    }

    public function setPedigree(?Pedigree $Pedigree): self
    {
        $this->Pedigree = $Pedigree;

        return $this;
    }

    public function getPedigreeCode(): ?string
    {
        return $this->pedigree_code;
    }

    public function setPedigreeCode(?string $pedigree_code): self
    {
        $this->pedigree_code = $pedigree_code;

        return $this;
    }

    public function getMicrochipCode(): ?string
    {
        return $this->microchip_code;
    }

    public function setMicrochipCode(?string $microchip_code): self
    {
        $this->microchip_code = $microchip_code;

        return $this;
    }

    public function getIsMicrochip(): ?string
    {
        return $this->is_microchip;
    }

    public function setIsMicrochip(?string $is_microchip): self
    {
        $this->is_microchip = $is_microchip;

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

    public function getPetCode(): ?string
    {
        return $this->pet_code;
    }

    public function setPetCode(?string $pet_code): self
    {
        $this->pet_code = $pet_code;

        return $this;
    }

    public function getBandColor(): ?int
    {
        return $this->band_color;
    }

    public function setBandColor(?int $band_color): self
    {
        $this->band_color = $band_color;

        return $this;
    }

    public function getPaymentMethod(): ?string
    {
        return $this->payment_method;
    }

    public function setPaymentMethod(?string $payment_method): self
    {
        $this->payment_method = $payment_method;

        return $this;
    }

    public function getReservationFee(): ?string
    {
        return $this->reservation_fee;
    }

    public function setReservationFee(?string $reservation_fee): self
    {
        $this->reservation_fee = $reservation_fee;

        return $this;
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
