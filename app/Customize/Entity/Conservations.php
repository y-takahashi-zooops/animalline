<?php

namespace Customize\Entity;

use Customize\Repository\ConservationsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\Mapping as ORM;
use Customize\Config\AnilineConf;

/**
 * @ORM\Table(name="alm_conservations")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discriminator_type", type="string", length=255)
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Entity(repositoryClass=ConservationsRepository::class)
 */
class Conservations extends \Eccube\Entity\AbstractEntity implements UserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(name="id", type="integer")
     */
    private $id;

    /**
     * @ORM\Column(name="user_id", type="integer", nullable=true)
     */
    private $user_id;

    /**
     * @ORM\Column(name="is_organization", type="smallint", nullable=true)
     */
    private $is_organization;

    /**
     * @ORM\Column(name="organization_name", type="string", length=255, nullable=true)
     */
    private $organization_name;

    /**
     * @ORM\Column(name="owner_name", type="string", length=255, nullable=true)
     */
    private $owner_name;

    /**
     * @ORM\Column(name="owner_kana", type="string", length=255, nullable=true)
     */
    private $owner_kana;

    /**
     * @ORM\Column(name="zip", type="string", length=7, nullable=true)
     */
    private $zip;

    /**
     * @var \Eccube\Entity\Master\Pref
     *
     * @ORM\ManyToOne(targetEntity="Eccube\Entity\Master\Pref")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="pref_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $PrefId;

    /**
     * @ORM\Column(name="pref", type="string", length=10, nullable=true)
     */
    private $pref;

    /**
     * @ORM\Column(name="city", type="string", length=10, nullable=true)
     */
    private $city;

    /**
     * @ORM\Column(name="address", type="string", length=255, nullable=true)
     */
    private $address;

    /**
     * @ORM\Column(name="building", type="string", length=255, nullable=true)
     */
    private $building;

    /**
     * @ORM\Column(name="tel", type="string", length=11, nullable=true)
     */
    private $tel;

    /**
     * @ORM\Column(name="fax", type="string", length=11, nullable=true)
     */
    private $fax;

    /**
     * @ORM\Column(name="homepage_url", type="string", length=255, nullable=true)
     */
    private $homepage_url;

    /**
     * @ORM\Column(name="is_active", type="smallint", nullable=true)
     */
    private $is_active;

    /**
     * @ORM\Column(name="examination_status", type="smallint", nullable=true)
     */
    private $examination_status;

    /**
     * @ORM\Column(name="pr_text", type="text", nullable=true)
     */
    private $pr_text;

    /**
     * @ORM\Column(name="email", type="string", length=255, nullable=true)
     */
    private $email;

    /**
     * @ORM\Column(name="password", type="string", length=255, nullable=true)
     */
    private $password;

    /**
     * @ORM\Column(name="register_status_id", type="smallint", length=5, nullable=true)
     */
    private $register_status_id;

    /**
     * @ORM\Column(name="salt", type="string", length=255, nullable=true)
     */
    private $salt;

    /**
     * @ORM\Column(name="secret_key", type="string", length=255)
     */
    private $secret_key;

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
     * @ORM\OneToMany(targetEntity=ConservationPets::class, mappedBy="Conservation")
     */
    private $ConservationPets;

    /**
     * @ORM\OneToMany(targetEntity=ConservationContacts::class, mappedBy="Conservation")
     */
    private $ConservationContacts;

    /**
     * @ORM\OneToMany(targetEntity=ConservationsHouse::class, mappedBy="Conservation")
     */
    private $ConservationsHouses;

    public function __construct()
    {
        $this->ConservationPets = new ArrayCollection();
        $this->ConservationContacts = new ArrayCollection();
        $this->ConservationsHouses = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserId(): ?int
    {
        return $this->user_id;
    }

    public function setUserId(?int $user_id): self
    {
        $this->user_id = $user_id;

        return $this;
    }

    public function getIsOrganization(): ?int
    {
        return $this->is_organization;
    }

    public function setIsOrganization(?int $is_organization): self
    {
        $this->is_organization = $is_organization;

        return $this;
    }

    public function getOrganizationName(): ?string
    {
        return $this->organization_name;
    }

    public function setOrganizationName(?string $organization_name): self
    {
        $this->organization_name = $organization_name;

        return $this;
    }

    public function getOwnerName(): ?string
    {
        return $this->owner_name;
    }

    public function setOwnerName(?string $owner_name): self
    {
        $this->owner_name = $owner_name;

        return $this;
    }

    public function getOwnerKana(): ?string
    {
        return $this->owner_kana;
    }

    public function setOwnerKana(?string $owner_kana): self
    {
        $this->owner_kana = $owner_kana;

        return $this;
    }

    public function getZip(): ?string
    {
        return $this->zip;
    }

    public function setZip(?string $zip): self
    {
        $this->zip = $zip;

        return $this;
    }

    public function setPrefId(\Eccube\Entity\Master\Pref $pref = null): Conservations
    {
        $this->PrefId = $pref;

        return $this;
    }

    public function getPrefId(): ?\Eccube\Entity\Master\Pref
    {
        return $this->PrefId;
    }

    public function getPref(): ?string
    {
        return $this->pref;
    }

    public function setPref(?string $pref): self
    {
        $this->pref = $pref;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getBuilding(): ?string
    {
        return $this->building;
    }

    public function setBuilding(?string $building): self
    {
        $this->building = $building;

        return $this;
    }

    public function getTel(): ?string
    {
        return $this->tel;
    }

    public function setTel(?string $tel): self
    {
        $this->tel = $tel;

        return $this;
    }

    public function getFax(): ?string
    {
        return $this->fax;
    }

    public function setFax(?string $fax): self
    {
        $this->fax = $fax;

        return $this;
    }

    public function getHomepageUrl(): ?string
    {
        return $this->homepage_url;
    }

    public function setHomepageUrl(?string $homepage_url): self
    {
        $this->homepage_url = $homepage_url;

        return $this;
    }

    public function getIsActive(): ?int
    {
        return $this->is_active;
    }

    public function setIsActive(?int $is_active): self
    {
        $this->is_active = $is_active;

        return $this;
    }

    public function getExaminationStatus(): ?int
    {
        return $this->examination_status;
    }

    public function setExaminationStatus(?int $examination_status): self
    {
        $this->examination_status = $examination_status;

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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

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

    public function setRegisterStatusId(?int $register_status_id): self
    {
        $this->register_status_id = $register_status_id;

        return $this;
    }

    public function getRegisterStatusId()
    {
        return $this->register_status_id;
    }

    /**
     * Set salt.
     *
     * @param string|null $salt
     *
     * @return Conservations
     */
    public function setSalt($salt = null)
    {
        $this->salt = $salt;

        return $this;
    }

    /**
     * Get salt.
     *
     * @return string|null
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * Set secretKey.
     *
     * @param string $secretKey
     *
     * @return Conservations
     */
    public function setSecretKey($secretKey)
    {
        $this->secret_key = $secretKey;

        return $this;
    }

    /**
     * Get secretKey.
     *
     * @return string
     */
    public function getSecretKey()
    {
        return $this->secret_key;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoles()
    {
        return ['ROLE_ADOPTION_USER'];
    }

    /**
     * {@inheritdoc}
     */
    public function getUsername()
    {
        return $this->email;
    }

    /**
     * {@inheritdoc}
     */
    public function eraseCredentials()
    {
    }

    public function equals(UserInterface $user)
    {
        return $this->getUsername() == $user->getUsername();
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
     * @return Conservations
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
     * @return Conservations
     */
    public function setUpdateDate($updateDate)
    {
        $this->update_date = $updateDate;

        return $this;
    }

    /**
     * @return Collection|ConservationPets[]
     */
    public function getConservationPets(): Collection
    {
        return $this->ConservationPets;
    }

    public function addConservationPet(ConservationPets $conservationPet): self
    {
        if (!$this->ConservationPets->contains($conservationPet)) {
            $this->ConservationPets[] = $conservationPet;
            $conservationPet->setConservation($this);
        }

        return $this;
    }

    public function removeConservationPet(ConservationPets $conservationPet): self
    {
        if ($this->ConservationPets->removeElement($conservationPet)) {
            // set the owning side to null (unless already changed)
            if ($conservationPet->getConservation() === $this) {
                $conservationPet->setConservation(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|ConservationContacts[]
     */
    public function getConservationContacts(): Collection
    {
        return $this->ConservationContacts;
    }

    public function addConservationContact(ConservationContacts $conservationContact): self
    {
        if (!$this->ConservationContacts->contains($conservationContact)) {
            $this->ConservationContacts[] = $conservationContact;
            $conservationContact->setConservation($this);
        }

        return $this;
    }

    public function removeConservationContact(ConservationContacts $conservationContact): self
    {
        if ($this->ConservationContacts->removeElement($conservationContact)) {
            // set the owning side to null (unless already changed)
            if ($conservationContact->getConservation() === $this) {
                $conservationContact->setConservation(null);
            }
        }

        return $this;
    }

    public function __toString()
    {
        return (string)$this->getId();
    }

    /**
     * @return Collection|ConservationsHouse[]
     */
    public function getConservationsHouses(): Collection
    {
        return $this->ConservationsHouses;
    }

    public function addConservationsHouse(ConservationsHouse $conservationsHouse): self
    {
        if (!$this->ConservationsHouses->contains($conservationsHouse)) {
            $this->ConservationsHouses[] = $conservationsHouse;
            $conservationsHouse->setConservation($this);
        }

        return $this;
    }

    public function removeConservationsHouse(ConservationsHouse $conservationsHouse): self
    {
        if ($this->ConservationsHouses->removeElement($conservationsHouse)) {
            // set the owning side to null (unless already changed)
            if ($conservationsHouse->getConservation() === $this) {
                $conservationsHouse->setConservation(null);
            }
        }

        return $this;
    }

    public function getConservationHouseByPetType($petType)
    {
        $result =  new ConservationsHouse();
        foreach ($this->ConservationsHouses as $house) {
            if ($house->getPetType() === $petType) {
                $result = $house;
                break;
            }
        }

        return $result;
    }
}
