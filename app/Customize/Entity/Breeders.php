<?php

namespace Customize\Entity;

use Customize\Repository\BreedersRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Eccube\Entity\Master\Pref;

/**
 * @ORM\Table(name="alm_breeders")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discriminator_type", type="string", length=255)
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Entity(repositoryClass=BreedersRepository::class)
 */
class Breeders 
{
    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     */
    private $id;

    /**
     * @ORM\Column(name="breeder_name", type="string", length=255, nullable=true)
     */
    private $breeder_name;

    /**
     * @ORM\Column(name="breeder_kana", type="string", length=255, nullable=true)
     */
    private $breeder_kana;

    /**
     * @ORM\Column(name="breeder_tel", type="string", length=11, nullable=true)
     */
    private $breeder_tel;

    /**
     * @ORM\Column(name="breeder_fax", type="string", length=11, nullable=true)
     */
    private $breeder_fax;

    /**
     * @ORM\Column(name="breeder_zip", type="string", length=7, nullable=true)
     */
    private $breeder_zip;

    /**
     * @ORM\ManyToOne(targetEntity="Eccube\Entity\Master\Pref", inversedBy="Breeders")
     * @ORM\JoinColumn(name="breeder_pref_id", nullable=true)
     */
    private $PrefBreeder;

    /**
     * @ORM\Column(name="breeder_pref", type="string", length=11, nullable=true)
     */
    private $breeder_pref;

    /**
     * @ORM\Column(name="breeder_city", type="string", length=11, nullable=true)
     */
    private $breeder_city;

    /**
     * @ORM\Column(name="breeder_address", type="string", length=255, nullable=true)
     */
    private $breeder_address;

    /**
     * @ORM\Column(name="breeder_rank", type="decimal",  nullable=true, precision=2, scale=1,  options={"default":0})
     */
    private $breeder_rank;

    /**
     * @ORM\Column(name="license_name", type="string", length=255, nullable=true)
     */
    private $license_name;

    /**
     * @ORM\Column(name="license_no", type="string", length=255, nullable=true)
     */
    private $license_no;

    /**
     * @ORM\Column(name="license_zip", type="string", length=7, nullable=true)
     */
    private $license_zip;

    /**
     * @ORM\ManyToOne(targetEntity="Eccube\Entity\Master\Pref", inversedBy="Breeders")
     * @ORM\JoinColumn(name="license_pref_id", nullable=true)
     */
    private $PrefLicense;

    /**
     * @ORM\Column(name="license_pref", type="string", length=10, nullable=true)
     */
    private $license_pref;

    /**
     * @ORM\Column(name="license_city", type="string", length=10, nullable=true)
     */
    private $license_city;

    /**
     * @ORM\Column(name="license_address", type="string", length=255, nullable=true)
     */
    private $license_address;

    /**
     * @ORM\Column(name="license_house_name", type="string", length=255, nullable=true)
     */
    private $license_house_name;

    /**
     * @ORM\Column(name="license_manager_name", type="string", length=255, nullable=true)
     */
    private $license_manager_name;

    /**
     * @ORM\Column(name="license_type", type="smallint", nullable=true)
     */
    private $license_type;

    /**
     * @ORM\Column(name="license_regist_date", type="date", nullable=true)
     */
    private $license_regist_date;

    /**
     * @ORM\Column(name="license_expire_date", type="date", nullable=true)
     */
    private $license_expire_date;

    /**
     * @ORM\Column(name="examination_status", type="smallint", options={"default" = 0})
     */
    private $examination_status = 0;

    /**
     * @ORM\Column(name="is_active", type="smallint", options={"default" = 0})
     */
    private $is_active = 0;

    /**
     * @ORM\Column(name="handling_pet_kind", type="smallint", options={"default" = 0})
     */
    private $handling_pet_kind = 0;

    /**
     * @ORM\Column(name="thumbnail_path", type="string", length=255, nullable=true)
     */
    private $thumbnail_path;

    /**
     * @ORM\Column(name="pr_text", type="text", nullable=true)
     */
    private $pr_text;

    /**
     * @ORM\Column(name="regal_effort", type="text", nullable=true)
     */
    private $regal_effort;

    /**
     * @ORM\Column(name="create_date", type="datetimetz", nullable=true)
     */
    private $create_date;

    /**
     * @ORM\Column(name="update_date", type="datetimetz", nullable=true)
     */
    private $update_date;

    /*
     * @ORM\OneToMany(targetEntity=BreederPets::class, mappedBy="Breeder")
     */
    private $BreederPets;

    /**
     * @ORM\OneToMany(targetEntity=BreederContacts::class, mappedBy="Breeder")
     */
    private $BreederContacts;

    /**
     * @ORM\OneToMany(targetEntity=BreederExaminationInfo::class, mappedBy="Breeder")
     */
    private $BreederExaminationInfos;

    /**
     * @ORM\OneToMany(targetEntity=BreederHouse::class, mappedBy="Breeder")
     */
    private $BreederHouses;

    public function __construct()
    {
        $this->BreederPets = new ArrayCollection();
        $this->BreederContacts = new ArrayCollection();
        $this->BreederExaminationInfos = new ArrayCollection();
        $this->BreederHouses = new ArrayCollection();
    }

    /**
     * @return Collection|BreederHouses[]
     */
    public function getBreederHouses(): Collection
    {
        return $this->BreederHouses;
    }

    public function getId(): ?int
    {
        return $this->id;
    }
    public function setId(?int $id): self
    {
        $this->id = $id;
        return $this;
    }


    public function getBreederName(): ?string
    {
        return $this->breeder_name;
    }

    public function setBreederName(?string $breeder_name): self
    {
        $this->breeder_name = $breeder_name;

        return $this;
    }

    public function getBreederKana(): ?string
    {
        return $this->breeder_kana;
    }

    public function setBreederKana(?string $breeder_kana): self
    {
        $this->breeder_kana = $breeder_kana;

        return $this;
    }

    public function getBreederTel(): ?string
    {
        return $this->breeder_tel;
    }

    public function setBreederTel(?string $breeder_tel): self
    {
        $this->breeder_tel = $breeder_tel;

        return $this;
    }

    public function getBreederFax(): ?string
    {
        return $this->breeder_fax;
    }

    public function setBreederFax(?string $breeder_fax): self
    {
        $this->breeder_fax = $breeder_fax;

        return $this;
    }

    public function getBreederZip(): ?string
    {
        return $this->breeder_zip;
    }

    public function setBreederZip(?string $breeder_zip): self
    {
        $this->breeder_zip = $breeder_zip;

        return $this;
    }

    public function getPrefBreeder(): ?Pref
    {
        return $this->PrefBreeder;
    }

    public function setPrefBreeder(?Pref $Pref): self
    {
        $this->PrefBreeder = $Pref;

        return $this;
    }

    public function getBreederPref(): ?string
    {
        return $this->breeder_pref;
    }

    public function setBreederPref(?string $breeder_pref): self
    {
        $this->breeder_pref = $breeder_pref;

        return $this;
    }

    public function getBreederCity(): ?string
    {
        return $this->breeder_city;
    }

    public function setBreederCity(?string $breeder_city): self
    {
        $this->breeder_city = $breeder_city;

        return $this;
    }

    public function getBreederAddress(): ?string
    {
        return $this->breeder_address;
    }

    public function setBreederAddress(?string $breeder_address): self
    {
        $this->breeder_address = $breeder_address;

        return $this;
    }

    public function getBreederRank()
    {
        return $this->breeder_rank;
    }

    public function setBreederRank($breeder_rank)
    {
        $this->breeder_rank = $breeder_rank;

        return $this;
    }

    public function getLicenseName(): ?string
    {
        return $this->license_name;
    }

    public function setLicenseName(?string $license_name): self
    {
        $this->license_name = $license_name;

        return $this;
    }

    public function getLicenseNo(): ?string
    {
        return $this->license_no;
    }

    public function setLicenseNo(?string $license_no): self
    {
        $this->license_no = $license_no;

        return $this;
    }

    public function getLicenseZip(): ?string
    {
        return $this->license_zip;
    }

    public function setLicenseZip(?string $license_zip): self
    {
        $this->license_zip = $license_zip;

        return $this;
    }

    public function getPrefLicense(): ?Pref
    {
        return $this->PrefLicense;
    }

    public function setPrefLicense(?Pref $Pref): self
    {
        $this->PrefLicense = $Pref;

        return $this;
    }

    public function getLicensePref(): ?string
    {
        return $this->license_pref;
    }

    public function setLicensePref(?string $license_pref): self
    {
        $this->license_pref = $license_pref;

        return $this;
    }

    public function getLicenseCity(): ?string
    {
        return $this->license_city;
    }

    public function setLicenseCity(?string $license_city): self
    {
        $this->license_city = $license_city;

        return $this;
    }

    public function getLicenseAddress(): ?string
    {
        return $this->license_address;
    }

    public function setLicenseAddress(?string $license_address): self
    {
        $this->license_address = $license_address;

        return $this;
    }

    public function getLicenseHouseName(): ?string
    {
        return $this->license_house_name;
    }

    public function setLicenseHouseName(?string $license_house_name): self
    {
        $this->license_house_name = $license_house_name;

        return $this;
    }

    public function getLicenseManagerName(): ?string
    {
        return $this->license_manager_name;
    }

    public function setLicenseManagerName(?string $license_manager_name): self
    {
        $this->license_manager_name = $license_manager_name;

        return $this;
    }

    public function getLicenseType(): ?int
    {
        return $this->license_type;
    }

    public function setLicenseType(?int $license_type): self
    {
        $this->license_type = $license_type;

        return $this;
    }

    public function getLicenseRegistDate(): ?\DateTimeInterface
    {
        return $this->license_regist_date;
    }

    public function setLicenseRegistDate(?\DateTimeInterface $license_regist_date): self
    {
        $this->license_regist_date = $license_regist_date;

        return $this;
    }

    public function getLicenseExpireDate(): ?\DateTimeInterface
    {
        return $this->license_expire_date;
    }

    public function setLicenseExpireDate(?\DateTimeInterface $license_expire_date): self
    {
        $this->license_expire_date = $license_expire_date;

        return $this;
    }

    public function getExaminationStatus(): ?int
    {
        return $this->examination_status;
    }

    public function setExaminationStatus(int $examination_status): self
    {
        $this->examination_status = $examination_status;

        return $this;
    }

    public function setHandlingPetKind(?int $handling_pet_kind): self
    {
        $this->handling_pet_kind = $handling_pet_kind;

        return $this;
    }

    public function getHandlingPetKind(): ?int
    {
        return $this->handling_pet_kind;
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

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;

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

    public function getPrText(): ?string
    {
        return $this->pr_text;
    }

    public function setPrText(?string $pr_text): self
    {
        $this->pr_text = $pr_text;

        return $this;
    }

    public function getRegalEffort(): ?string
    {
        return $this->regal_effort;
    }

    public function setRegalEffort(?string $regal_effort): self
    {
        $this->regal_effort = $regal_effort;

        return $this;
    }

    public function setCreateDate($createDate)
    {
        $this->create_date = $createDate;

        return $this;
    }

    public function setUpdateDate($updateDate)
    {
        $this->update_date = $updateDate;

        return $this;
    }

    /**
     * @return Collection|BreederContacts[]
     */
    public function getBreederContacts(): Collection
    {
        return $this->BreederContacts;
    }

    public function addBreederContact(BreederContacts $breederContact): self
    {
        if (!$this->BreederContacts->contains($breederContact)) {
            $this->BreederContacts[] = $breederContact;
            $breederContact->setBreeder($this);
        }

        return $this;
    }

    public function removeBreederContact(BreederContacts $breederContact): self
    {
        if ($this->BreederContacts->removeElement($breederContact)) {
            // set the owning side to null (unless already changed)
            if ($breederContact->getBreeder() === $this) {
                $breederContact->setBreeder(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|BreederPets[]
     */
    public function getBreederPets(): Collection
    {
        return $this->BreederPets;
    }

    public function addBreederPet(BreederPets $breederPet): self
    {
        if (!$this->BreederPets->contains($breederPet)) {
            $this->BreederPets[] = $breederPet;
            $breederPet->setBreeder($this);
        }

        return $this;
    }

    public function removeBreederPet(BreederPets $breederPet): self
    {
        if ($this->BreederPets->removeElement($breederPet)) {
            // set the owning side to null (unless already changed)
            if ($breederPet->getBreeder() === $this) {
                $breederPet->setBreeder(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|BreederExaminationInfo[]
     */
    public function getBreederExaminationInfos(): Collection
    {
        return $this->BreederExaminationInfos;
    }

    public function addBreederExaminationInfo(BreederExaminationInfo $breederExaminationInfo): self
    {
        if (!$this->BreederExaminationInfos->contains($breederExaminationInfo)) {
            $this->BreederExaminationInfos[] = $breederExaminationInfo;
            $breederExaminationInfo->setBreeder($this);
        }

        return $this;
    }

    public function removeBreederExaminationInfo(BreederExaminationInfo $breederExaminationInfo): self
    {
        if ($this->BreederExaminationInfos->removeElement($breederExaminationInfo)) {
            // set the owning side to null (unless already changed)
            if ($breederExaminationInfo->getBreeder() === $this) {
                $breederExaminationInfo->setBreeder(null);
            }
        }

        return $this;
    }

    public function getBreederHouseByPetType($petType)
    {
        $result =  new BreederHouse();
        foreach ($this->BreederHouses as $house) {
            if ($house->getPetType() === $petType) {
                $result = $house;
                break;
            }
        }

        return $result;
    }

    public function getCreateDate()
    {
        return $this->create_date;
    }

    public function getUpdateDate()
    {
        return $this->update_date;
    }
}
