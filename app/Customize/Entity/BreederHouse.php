<?php

namespace Customize\Entity;

use Customize\Repository\BreederHouseRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=BreederHouseRepository::class)
 * @ORM\Table(name="alm_breeder_house")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discriminator_type", type="string", length=255)
 * @ORM\HasLifecycleCallbacks()
 */
class BreederHouse
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(name="id", type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Breeders::class, inversedBy="BreederHouses")
     * @ORM\JoinColumn(name="breeder_id", referencedColumnName="id", nullable=false)
     */
    private $Breeder;

    /**
     * @ORM\Column(name="pet_type", type="smallint", nullable=false)
     */
    private $pet_type;

    /**
     * @ORM\Column(name="breeder_house_house_tel", type="string", length=11, nullable=true)
     */
    private $breeder_house_house_tel;

    /**
     * @ORM\Column(name="breeder_house_house_fax", type="string", length=11, nullable=true)
     */
    private $breeder_house_house_fax;

    /**
     * @ORM\Column(name="breeder_house_house_zip", type="string", length=7, nullable=true)
     */
    private $breeder_house_house_zip;

    /**
     * @var \Eccube\Entity\Master\Pref
     *
     * @ORM\ManyToOne(targetEntity="Eccube\Entity\Master\Pref")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="breeder_house_pref_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $BreederHousePrefId;

    /**
     * @ORM\Column(name="breeder_house_pref", type="string", length=11, nullable=true)
     */
    private $breeder_house_pref;

    /**
     * @ORM\Column(name="breeder_house_city", type="string", length=11, nullable=true)
     */
    private $breeder_house_city;

    /**
     * @ORM\Column(name="breeder_house_address", type="string", length=255, nullable=true)
     */
    private $breeder_house_address;

    /**
     * @ORM\Column(name="breeder_house_front_name", type="string", length=255, nullable=true)
     */
    private $breeder_house_front_name;

    /**
     * @ORM\Column(name="breeder_house_front_tel", type="string", length=11, nullable=true)
     */
    private $breeder_house_front_tel;

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

    public function getBreeder(): ?Breeders
    {
        return $this->Breeder;
    }

    public function setBreeder(?Breeders $breeder): self
    {
        $this->Breeder = $breeder;

        return $this;
    }

    public function getPetType(): ?int
    {
        return $this->pet_type;
    }

    public function setPetType(int $pet_type): self
    {
        $this->pet_type = $pet_type;

        return $this;
    }

    public function getBreederHouseHouseTel(): ?string
    {
        return $this->breeder_house_house_tel;
    }

    public function setBreederHouseHouseTel(?string $breeder_house_house_tel): self
    {
        $this->breeder_house_house_tel = $breeder_house_house_tel;

        return $this;
    }

    public function getBreederHouseHouseFax(): ?string
    {
        return $this->breeder_house_house_fax;
    }

    public function setBreederHouseHouseFax(?string $breeder_house_house_fax): self
    {
        $this->breeder_house_house_fax = $breeder_house_house_fax;

        return $this;
    }

    public function getBreederHouseHouseZip(): ?string
    {
        return $this->breeder_house_house_zip;
    }

    public function setBreederHouseHouseZip(?string $breeder_house_house_zip): self
    {
        $this->breeder_house_house_zip = $breeder_house_house_zip;

        return $this;
    }

    /**
     * Set pref.
     *
     * @param \Eccube\Entity\Master\Pref|null $pref
     *
     * @return BreederHouse
     */
    public function setBreederHousePrefId(\Eccube\Entity\Master\Pref $pref = null): BreederHouse
    {
        $this->BreederHousePrefId = $pref;

        return $this;
    }

    /**
     * Get pref.
     *
     * @return \Eccube\Entity\Master\Pref|null
     */
    public function getBreederHousePrefId(): ?\Eccube\Entity\Master\Pref
    {
        return $this->BreederHousePrefId;
    }

    public function getBreederHousePref(): ?string
    {
        return $this->breeder_house_pref;
    }

    public function setBreederHousePref(?string $breeder_house_pref): self
    {
        $this->breeder_house_pref = $breeder_house_pref;

        return $this;
    }

    public function getBreederHouseCity(): ?string
    {
        return $this->breeder_house_city;
    }

    public function setBreederHouseCity(?string $breeder_house_city): self
    {
        $this->breeder_house_city = $breeder_house_city;

        return $this;
    }

    public function getBreederHouseAddress(): ?string
    {
        return $this->breeder_house_address;
    }

    public function setBreederHouseAddress(?string $breeder_house_address): self
    {
        $this->breeder_house_address = $breeder_house_address;

        return $this;
    }

    public function getBreederHouseFrontName(): ?string
    {
        return $this->breeder_house_front_name;
    }

    public function setBreederHouseFrontName(?string $breeder_house_front_name): self
    {
        $this->breeder_house_front_name = $breeder_house_front_name;

        return $this;
    }

    public function getBreederHouseFrontTel(): ?string
    {
        return $this->breeder_house_front_tel;
    }

    public function setBreederHouseFrontTel(?string $breeder_house_front_tel): self
    {
        $this->breeder_house_front_tel = $breeder_house_front_tel;

        return $this;
    }

    /**
     * Set createDate.
     *
     * @param \DateTime $createDate
     *
     * @return BreederHouse
     */
    public function setCreateDate(\DateTime $createDate): BreederHouse
    {
        $this->create_date = $createDate;

        return $this;
    }

    /**
     * Set updateDate.
     *
     * @param \DateTime $updateDate
     *
     * @return BreederHouse
     */
    public function setUpdateDate(\DateTime $updateDate): BreederHouse
    {
        $this->update_date = $updateDate;

        return $this;
    }
}
