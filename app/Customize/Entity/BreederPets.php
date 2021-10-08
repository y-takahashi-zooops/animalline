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
     * @ORM\ManyToOne(targetEntity="Customize\Entity\CoatColors", inversedBy="BreederPets")
     * @ORM\JoinColumn(name="coat_color", nullable=true)
     */
    private $CoatColor;

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
     * @ORM\Column(name="delivery_way", type="text", nullable=true)
     */
    private $delivery_way;

    /**
     * @ORM\Column(name="price", type="integer")
     */
    private $price;

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
     * @ORM\OneToMany(targetEntity=BreederContactHeader::class, mappedBy="pet_id")
     */
    private $BreederContactHeader;

    /**
     * @ORM\OneToMany(targetEntity=BreederEvaluations::class, mappedBy="pet_id")
     */
    private $BreederEvaluations;

    /**
     * @ORM\ManyToOne(targetEntity=Pedigree::class)
     * @ORM\JoinColumn(name="pedigree_id", nullable=true)
     */
    private $Pedigree;

    /**
     * @ORM\Column(name="pedigree_code", type="integer", nullable=true)
     */
    private $pedigree_code;
    
    /**
     * @ORM\Column(name="microchip_code ", type="integer", nullable=true)
     */
    private $microchip_code;


    public function __construct()
    {
        $this->BreederPetImages = new ArrayCollection();
        $this->PetsFavorites = new ArrayCollection();
        $this->BreederContactHeader = new ArrayCollection();
        $this->BreederEvaluations = new ArrayCollection();
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

    public function setBreedsType(Breeds $breeds_type): self
    {
        $this->BreedsType = $breeds_type;

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
        return $this->CoatColor;
    }

    public function setCoatColor(?CoatColors $CoatColor): self
    {
        $this->CoatColor = $CoatColor;

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

    public function getVaccineDetail(): ?string
    {
        return $this->vaccine_detail;
    }

    public function setVaccineDetail(string $vaccine_detail): self
    {
        $this->vaccine_detail = $vaccine_detail;

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
            $breederPetImage->setBreederPetId($this);
        }

        return $this;
    }

    public function removeBreederPetImage(BreederPetImage $breederPetImage): self
    {
        if ($this->BreederPetImages->removeElement($breederPetImage)) {
            // set the owning side to null (unless already changed)
            if ($breederPetImage->getBreederPetId() === $this) {
                $breederPetImage->setBreederPetId(null);
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

    public function setFavoriteCount(int $favorite_count): self
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
    
    public function getPedigreeCode(): ?int
    {
        return $this->pedigree_code;
    }

    public function setPedigreeCode(int $pedigree_code): self
    {
        $this->pedigree_code = $pedigree_code;

        return $this;
    }
    
    public function getMicrochipCode (): ?int
    {
        return $this->microchip_code ;
    }

    public function setMicrochipCode(int $microchip_code ): self
    {
        $this->microchip_code  = $microchip_code ;

        return $this;
    }
}
