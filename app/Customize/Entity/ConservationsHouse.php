<?php

namespace Customize\Entity;

use Customize\Repository\ConservationsHousesRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ConservationsHousesRepository::class)
 * @ORM\Table(name="alm_conservations_house")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discriminator_type", type="string", length=255)
 * @ORM\HasLifecycleCallbacks()
 */
class ConservationsHouse
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(name="id", type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Conservations::class, inversedBy="ConservationsHouses")
     * @ORM\JoinColumn(name="conservation_id", referencedColumnName="id", nullable=false)
     */
    private $Conservation;

    /**
     * @ORM\Column(name="pet_type", type="smallint", nullable=false)
     */
    private $pet_type;

    /**
     * @ORM\Column(name="conservation_house_name", type="string", length=255, nullable=true)
     */
    private $conservation_house_name;

    /**
     * @ORM\Column(name="conservation_house_kana", type="string", length=255, nullable=true)
     */
    private $conservation_house_kana;

    /**
     * @ORM\Column(name="conservation_house_house_tel", type="string", length=255, nullable=true)
     */
    private $conservation_house_house_tel;

    /**
     * @ORM\Column(name="conservation_house_house_fax", type="string", length=255, nullable=true)
     */
    private $conservation_house_house_fax;

    /**
     * @ORM\Column(name="conservation_house_house_zip", type="string", length=255, nullable=true)
     */
    private $conservation_house_house_zip;

    /**
     * @var \Eccube\Entity\Master\Pref
     *
     * @ORM\ManyToOne(targetEntity="Eccube\Entity\Master\Pref")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="conservation_house_pref_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $Pref;

    /**
     * @ORM\Column(name="conservation_house_pref", type="string", length=11, nullable=true)
     */
    private $conservation_house_pref;

    /**
     * @ORM\Column(name="conservation_house_city", type="string", length=11, nullable=true)
     */
    private $conservation_house_city;

    /**
     * @ORM\Column(name="conservation_house_address", type="string", length=255, nullable=true)
     */
    private $conservation_house_address;

    /**
     * @ORM\Column(name="conservation_house_building", type="string", length=255, nullable=true)
     */
    private $conservation_house_building;

    /**
     * @ORM\Column(name="conservation_house_front_name", type="string", length=255, nullable=true)
     */
    private $conservation_house_front_name;

    /**
     * @ORM\Column(name="conservation_house_front_tel", type="string", length=11, nullable=true)
     */
    private $conservation_house_front_tel;

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

    public function getConservation(): ?Conservations
    {
        return $this->Conservation;
    }

    public function setConservation(?Conservations $Conservation): self
    {
        $this->Conservation = $Conservation;

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

    public function getConservationHouseName(): ?string
    {
        return $this->conservation_house_name;
    }

    public function setConservationHouseName(?string $conservation_house_name): self
    {
        $this->conservation_house_name = $conservation_house_name;

        return $this;
    }

    public function getConservationHouseKana(): ?string
    {
        return $this->conservation_house_kana;
    }

    public function setConservationHouseKana(?string $conservation_house_kana): self
    {
        $this->conservation_house_kana = $conservation_house_kana;

        return $this;
    }

    public function getConservationHouseHouseTel(): ?string
    {
        return $this->conservation_house_house_tel;
    }

    public function setConservationHouseHouseTel(?string $conservation_house_house_tel): self
    {
        $this->conservation_house_house_tel = $conservation_house_house_tel;

        return $this;
    }

    public function getConservationHouseHouseFax(): ?string
    {
        return $this->conservation_house_house_fax;
    }

    public function setConservationHouseHouseFax(?string $conservation_house_house_fax): self
    {
        $this->conservation_house_house_fax = $conservation_house_house_fax;

        return $this;
    }

    public function getConservationHouseHouseZip(): ?string
    {
        return $this->conservation_house_house_zip;
    }

    public function setConservationHouseHouseZip(?string $conservation_house_house_zip): self
    {
        $this->conservation_house_house_zip = $conservation_house_house_zip;

        return $this;
    }

    /**
     * Set pref.
     *
     * @param \Eccube\Entity\Master\Pref|null $pref
     *
     * @return ConservationsHouse
     */
    public function setPref(\Eccube\Entity\Master\Pref $pref = null): ConservationsHouse
    {
        $this->Pref = $pref;

        return $this;
    }

    /**
     * Get pref.
     *
     * @return \Eccube\Entity\Master\Pref|null
     */
    public function getPref(): ?\Eccube\Entity\Master\Pref
    {
        return $this->Pref;
    }

    public function getConservationHousePref(): ?string
    {
        return $this->conservation_house_pref;
    }

    public function setConservationHousePref(?string $conservation_house_pref): self
    {
        $this->conservation_house_pref = $conservation_house_pref;

        return $this;
    }

    public function getConservationHouseCity(): ?string
    {
        return $this->conservation_house_city;
    }

    public function setConservationHouseCity(?string $conservation_house_city): self
    {
        $this->conservation_house_city = $conservation_house_city;

        return $this;
    }

    public function getConservationHouseAddress(): ?string
    {
        return $this->conservation_house_address;
    }

    public function setConservationHouseAddress(?string $conservation_house_address): self
    {
        $this->conservation_house_address = $conservation_house_address;

        return $this;
    }

    public function getConservationHouseBuilding(): ?string
    {
        return $this->conservation_house_building;
    }

    public function setConservationHouseBuilding(?string $conservation_house_building): self
    {
        $this->conservation_house_building = $conservation_house_building;

        return $this;
    }

    public function getConservationHouseFrontName(): ?string
    {
        return $this->conservation_house_front_name;
    }

    public function setConservationHouseFrontName(?string $conservation_house_front_name): self
    {
        $this->conservation_house_front_name = $conservation_house_front_name;

        return $this;
    }

    public function getConservationHouseFrontTel(): ?string
    {
        return $this->conservation_house_front_tel;
    }

    public function setConservationHouseFrontTel(?string $conservation_house_front_tel): self
    {
        $this->conservation_house_front_tel = $conservation_house_front_tel;

        return $this;
    }

    /**
     * Set createDate.
     *
     * @param \DateTime $createDate
     *
     * @return ConservationsHouse
     */
    public function setCreateDate(\DateTime $createDate): ConservationsHouse
    {
        $this->create_date = $createDate;

        return $this;
    }

    /**
     * Set updateDate.
     *
     * @param \DateTime $updateDate
     *
     * @return ConservationsHouse
     */
    public function setUpdateDate(\DateTime $updateDate): ConservationsHouse
    {
        $this->update_date = $updateDate;

        return $this;
    }
}
