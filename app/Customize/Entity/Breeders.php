<?php

namespace Customize\Entity;

use Customize\Repository\BreedersRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\Mapping as ORM;
use Customize\Config\AnilineConf;

/**
 * @ORM\Table(name="alm_breeders")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discriminator_type", type="string", length=255)
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Entity(repositoryClass=BreedersRepository::class)
 */
class Breeders extends \Eccube\Entity\AbstractEntity implements UserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(name="id", type="integer")
     */
    private $id;

    /**
     * @ORM\Column(name="breeder_house_name", type="string", length=255, nullable=true)
     */
    private $breeder_house_name;

    /**
     * @ORM\Column(name="owner_name", type="string", length=255, nullable=true)
     */
    private $owner_name;

    /**
     * @ORM\Column(name="owner_kana", type="string", length=255, nullable=true)
     */
    private $owner_kana;

    /**
     * @ORM\Column(name="breeder_house_tel", type="string", length=11, nullable=true)
     */
    private $breeder_house_tel;

    /**
     * @ORM\Column(name="breeder_house_fax", type="string", length=11, nullable=true)
     */
    private $breeder_house_fax;

    /**
     * @ORM\Column(name="breeder_house_zip", type="string", length=7, nullable=true)
     */
    private $breeder_house_zip;

    /**
     * @ORM\Column(name="breeder_house_pref", type="string", length=10, nullable=true)
     */
    private $breeder_house_pref;

    /**
     * @ORM\Column(name="breeder_house_city", type="string", length=10, nullable=true)
     */
    private $breeder_house_city;

    /**
     * @ORM\Column(name="breeder_house_address", type="string", length=255, nullable=true)
     */
    private $breeder_house_address;

    /**
     * @ORM\Column(name="breeder_house_building", type="string", length=255, nullable=true)
     */
    private $breeder_house_building;

    /**
     * @ORM\Column(name="responsible_name", type="string", length=255, nullable=true)
     */
    private $responsible_name;

    /**
     * @ORM\Column(name="responsible_kana", type="string", length=255, nullable=true)
     */
    private $responsible_kana;

    /**
     * @ORM\Column(name="responsible_zip", type="string", length=7, nullable=true)
     */
    private $responsible_zip;

    /**
     * @ORM\Column(name="responsible_pref", type="string", length=10, nullable=true)
     */
    private $responsible_pref;

    /**
     * @ORM\Column(name="responsible_city", type="string", length=10, nullable=true)
     */
    private $responsible_city;

    /**
     * @ORM\Column(name="responsible_address", type="string", length=255, nullable=true)
     */
    private $responsible_address;

    /**
     * @ORM\Column(name="office_name", type="string", length=255, nullable=true)
     */
    private $office_name;

    /**
     * @ORM\Column(name="authorization_type", type="smallint", nullable=true)
     */
    private $authorization_type;

    /**
     * @ORM\Column(name="pet_parent_count", type="smallint", nullable=true)
     */
    private $pet_parent_count;

    /**
     * @ORM\Column(name="staff_count_1", type="smallint", nullable=true)
     */
    private $staff_count_1;

    /**
     * @ORM\Column(name="staff_count_2", type="smallint", nullable=true)
     */
    private $staff_count_2;

    /**
     * @ORM\Column(name="staff_count_3", type="smallint", nullable=true)
     */
    private $staff_count_3;

    /**
     * @ORM\Column(name="staff_count_4", type="smallint", nullable=true)
     */
    private $staff_count_4;

    /**
     * @ORM\Column(name="breed_exp_year", type="smallint", nullable=true)
     */
    private $breed_exp_year;

    /**
     * @ORM\Column(name="is_participation_show", type="smallint", nullable=true)
     */
    private $is_participation_show;

    /**
     * @ORM\Column(name="cage_size", type="smallint", nullable=true)
     */
    private $cage_size;

    /**
     * @ORM\Column(name="pet_exercise_env", type="smallint", nullable=true)
     */
    private $pet_exercise_env;

    /**
     * @ORM\Column(name="can_publish_count", type="smallint", nullable=true)
     */
    private $can_publish_count;

    /**
     * @ORM\Column(name="self_breed_exp_year", type="smallint", nullable=true)
     */
    private $self_breed_exp_year;

    /**
     * @ORM\Column(name="direct_sell_exp", type="smallint", nullable=true)
     */
    private $direct_sell_exp;

    /**
     * @ORM\Column(name="is_pet_trade", type="smallint", nullable=true)
     */
    private $is_pet_trade;

    /**
     * @ORM\Column(name="sell_route", type="string", length=255, nullable=true)
     */
    private $sell_route;

    /**
     * @ORM\Column(name="is_full_time", type="smallint", nullable=true)
     */
    private $is_full_time;

    /**
     * @ORM\Column(name="homepage_url", type="string", length=255, nullable=true)
     */
    private $homepage_url;

    /**
     * @ORM\Column(name="sns_url", type="string", length=255, nullable=true)
     */
    private $sns_url;

    /**
     * @ORM\Column(name="regist_reason", type="text", nullable=true)
     */
    private $regist_reason;

    /**
     * @ORM\Column(name="free_comment", type="text", nullable=true)
     */
    private $free_comment;

    /**
     * @ORM\Column(name="introducer_name", type="string", length=255, nullable=true)
     */
    private $introducer_name;

    /**
     * @ORM\Column(name="examination_status", type="smallint", nullable=true)
     */
    private $examination_status;

    /**
     * @ORM\Column(name="is_active", type="smallint", nullable=true)
     */
    private $is_active;

    /**
     * @ORM\Column(name="password", type="string", length=255, nullable=true)
     */
    private $password;

    /**
     * @ORM\Column(name="email", type="string", length=255, nullable=true)
     */
    private $email;

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

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBreederHouseName(): ?string
    {
        return $this->breeder_house_name;
    }

    public function setBreederHouseName(?string $breeder_house_name): self
    {
        $this->breeder_house_name = $breeder_house_name;

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

    public function getBreederHouseTel(): ?string
    {
        return $this->breeder_house_tel;
    }

    public function setBreederHouseTel(?string $breeder_house_tel): self
    {
        $this->breeder_house_tel = $breeder_house_tel;

        return $this;
    }

    public function getBreederHouseFax(): ?string
    {
        return $this->breeder_house_fax;
    }

    public function setBreederHouseFax(?string $breeder_house_fax): self
    {
        $this->breeder_house_fax = $breeder_house_fax;

        return $this;
    }

    public function getBreederHouseZip(): ?string
    {
        return $this->breeder_house_zip;
    }

    public function setBreederHouseZip(?string $breeder_house_zip): self
    {
        $this->breeder_house_zip = $breeder_house_zip;

        return $this;
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

    public function getBreederHouseBuilding(): ?string
    {
        return $this->breeder_house_building;
    }

    public function setBreederHouseBuilding(string $breeder_house_building): self
    {
        $this->breeder_house_building = $breeder_house_building;

        return $this;
    }

    public function getResponsibleName(): ?string
    {
        return $this->responsible_name;
    }

    public function setResponsibleName(?string $responsible_name): self
    {
        $this->responsible_name = $responsible_name;

        return $this;
    }

    public function getResponsibleKana(): ?string
    {
        return $this->responsible_kana;
    }

    public function setResponsibleKana(?string $responsible_kana): self
    {
        $this->responsible_kana = $responsible_kana;

        return $this;
    }

    public function getResponsibleZip(): ?string
    {
        return $this->responsible_zip;
    }

    public function setResponsibleZip(?string $responsible_zip): self
    {
        $this->responsible_zip = $responsible_zip;

        return $this;
    }

    public function getResponsiblePref(): ?string
    {
        return $this->responsible_pref;
    }

    public function setResponsiblePref(?string $responsible_pref): self
    {
        $this->responsible_pref = $responsible_pref;

        return $this;
    }

    public function getResponsibleCity(): ?string
    {
        return $this->responsible_city;
    }

    public function setResponsibleCity(?string $responsible_city): self
    {
        $this->responsible_city = $responsible_city;

        return $this;
    }

    public function getResponsibleAddress(): ?string
    {
        return $this->responsible_address;
    }

    public function setResponsibleAddress(?string $responsible_address): self
    {
        $this->responsible_address = $responsible_address;

        return $this;
    }

    public function getOfficeName(): ?string
    {
        return $this->office_name;
    }

    public function setOfficeName(?string $office_name): self
    {
        $this->office_name = $office_name;

        return $this;
    }

    public function getAuthorizationType(): ?int
    {
        return $this->authorization_type;
    }

    public function setAuthorizationType(?int $authorization_type): self
    {
        $this->authorization_type = $authorization_type;

        return $this;
    }

    public function getPetParentCount(): ?int
    {
        return $this->pet_parent_count;
    }

    public function setPetParentCount(?int $pet_parent_count): self
    {
        $this->pet_parent_count = $pet_parent_count;

        return $this;
    }

    public function getStaffCount1(): ?int
    {
        return $this->staff_count_1;
    }

    public function setStaffCount1(?int $staff_count_1): self
    {
        $this->staff_count_1 = $staff_count_1;

        return $this;
    }

    public function getStaffCount2(): ?int
    {
        return $this->staff_count_2;
    }

    public function setStaffCount2(?int $staff_count_2): self
    {
        $this->staff_count_2 = $staff_count_2;

        return $this;
    }

    public function getStaffCount3(): ?int
    {
        return $this->staff_count_3;
    }

    public function setStaffCount3(?int $staff_count_3): self
    {
        $this->staff_count_3 = $staff_count_3;

        return $this;
    }

    public function getStaffCount4(): ?int
    {
        return $this->staff_count_4;
    }

    public function setStaffCount4(?int $staff_count_4): self
    {
        $this->staff_count_4 = $staff_count_4;

        return $this;
    }

    public function getBreedExpYear(): ?int
    {
        return $this->breed_exp_year;
    }

    public function setBreedExpYear(?int $breed_exp_year): self
    {
        $this->breed_exp_year = $breed_exp_year;

        return $this;
    }

    public function getIsParticipationShow(): ?int
    {
        return $this->is_participation_show;
    }

    public function setIsParticipationShow(?int $is_participation_show): self
    {
        $this->is_participation_show = $is_participation_show;

        return $this;
    }

    public function getCageSize(): ?int
    {
        return $this->cage_size;
    }

    public function setCageSize(?int $cage_size): self
    {
        $this->cage_size = $cage_size;

        return $this;
    }

    public function getPetExerciseEnv(): ?int
    {
        return $this->pet_exercise_env;
    }

    public function setPetExerciseEnv(?int $pet_exercise_env): self
    {
        $this->pet_exercise_env = $pet_exercise_env;

        return $this;
    }

    public function getCanPublishCount(): ?int
    {
        return $this->can_publish_count;
    }

    public function setCanPublishCount(?int $can_publish_count): self
    {
        $this->can_publish_count = $can_publish_count;

        return $this;
    }

    public function getSelfBreedExpYear(): ?int
    {
        return $this->self_breed_exp_year;
    }

    public function setSelfBreedExpYear(?int $self_breed_exp_year): self
    {
        $this->self_breed_exp_year = $self_breed_exp_year;

        return $this;
    }

    public function getDirectSellExp(): ?int
    {
        return $this->direct_sell_exp;
    }

    public function setDirectSellExp(?int $direct_sell_exp): self
    {
        $this->direct_sell_exp = $direct_sell_exp;

        return $this;
    }

    public function getIsPetTrade(): ?int
    {
        return $this->is_pet_trade;
    }

    public function setIsPetTrade(?int $is_pet_trade): self
    {
        $this->is_pet_trade = $is_pet_trade;

        return $this;
    }

    public function getSellRoute(): ?string
    {
        return $this->sell_route;
    }

    public function setSellRoute(?string $sell_route): self
    {
        $this->sell_route = $sell_route;

        return $this;
    }

    public function getIsFullTime(): ?int
    {
        return $this->is_full_time;
    }

    public function setIsFullTime(?int $is_full_time): self
    {
        $this->is_full_time = $is_full_time;

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

    public function getSnsUrl(): ?string
    {
        return $this->sns_url;
    }

    public function setSnsUrl(?string $sns_url): self
    {
        $this->sns_url = $sns_url;

        return $this;
    }

    public function getRegistReason(): ?string
    {
        return $this->regist_reason;
    }

    public function setRegistReason(?string $regist_reason): self
    {
        $this->regist_reason = $regist_reason;

        return $this;
    }

    public function getFreeComment(): ?string
    {
        return $this->free_comment;
    }

    public function setFreeComment(?string $free_comment): self
    {
        $this->free_comment = $free_comment;

        return $this;
    }

    public function getIntroducerName(): ?string
    {
        return $this->introducer_name;
    }

    public function setIntroducerName(?string $introducer_name): self
    {
        $this->introducer_name = $introducer_name;

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

    public function getIsActive(): ?int
    {
        return $this->is_active;
    }

    public function setIsActive(?int $is_active): self
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

    
    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

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

    public function setRegisterStatusId(int $register_status_id)
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
     * @return Customer
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
     * @return Customer
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
        return ['ROLE_BREEDER_USER'];
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

    public function __toString()
    {
        return (string) $this->getId();
    }
}
