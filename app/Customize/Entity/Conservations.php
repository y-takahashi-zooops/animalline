<?php

namespace Customize\Entity;

use Customize\Repository\ConservationsRepository;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\Mapping as ORM;

/**     
 * @ORM\Table(name="alm_adoptions")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discriminator_type", type="string", length=255)
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Entity(repositoryClass=ConservationsRepository::class)
 * @ORM\Table(name="alm_adoptions")
 */
class Conservations extends \Eccube\Entity\AbstractEntity implements UserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $user_id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $adoption_house_name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $owner_name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $owner_kana;

    /**
     * @ORM\Column(type="string", length=7, nullable=true)
     */
    private $adoption_house_zip;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private $adoption_house_pref;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private $adoption_house_city;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $adoption_house_address;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $adoption_house_building;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private $adoption_house_tel;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private $adoption_house_fax;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $homepage_url;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $sns_url;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $is_active;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $examination_status;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $regist_reason;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $free_comment;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $can_publish_count;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $pet_exercise_env;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $cage_size;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $adoption_exp_year;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $staff_count_1;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $staff_count_2;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $staff_count_3;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $staff_count_4;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $password;

    /**
     * @var \Eccube\Entity\Master\CustomerStatus
     *
     * @ORM\ManyToOne(targetEntity="Eccube\Entity\Master\CustomerStatus")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="customer_status_id", referencedColumnName="id")
     * })
     */
    private $Status;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $salt;

    /**
     * @ORM\Column(name="secret_key", type="string", length=255)
     */
    private $secret_key;

    private $discriminator_type;

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

    public function getAdoptionHouseName(): ?string
    {
        return $this->adoption_house_name;
    }

    public function setAdoptionHouseName(?string $adoption_house_name): self
    {
        $this->adoption_house_name = $adoption_house_name;

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

    public function getAdoptionHouseZip(): ?string
    {
        return $this->adoption_house_zip;
    }

    public function setAdoptionHouseZip(?string $adoption_house_zip): self
    {
        $this->adoption_house_zip = $adoption_house_zip;

        return $this;
    }

    public function getAdoptionHousePref(): ?string
    {
        return $this->adoption_house_pref;
    }

    public function setAdoptionHousePref(?string $adoption_house_pref): self
    {
        $this->adoption_house_pref = $adoption_house_pref;

        return $this;
    }

    public function getAdoptionHouseCity(): ?string
    {
        return $this->adoption_house_city;
    }

    public function setAdoptionHouseCity(?string $adoption_house_city): self
    {
        $this->adoption_house_city = $adoption_house_city;

        return $this;
    }

    public function getAdoptionHouseAddress(): ?string
    {
        return $this->adoption_house_address;
    }

    public function setAdoptionHouseAddress(?string $adoption_house_address): self
    {
        $this->adoption_house_address = $adoption_house_address;

        return $this;
    }

    public function getAdoptionHouseBuilding(): ?string
    {
        return $this->adoption_house_building;
    }

    public function setAdoptionHouseBuilding(?string $adoption_house_building): self
    {
        $this->adoption_house_building = $adoption_house_building;

        return $this;
    }

    public function getAdoptionHouseTel(): ?string
    {
        return $this->adoption_house_tel;
    }

    public function setAdoptionHouseTel(?string $adoption_house_tel): self
    {
        $this->adoption_house_tel = $adoption_house_tel;

        return $this;
    }

    public function getAdoptionHouseFax(): ?string
    {
        return $this->adoption_house_fax;
    }

    public function setAdoptionHouseFax(?string $adoption_house_fax): self
    {
        $this->adoption_house_fax = $adoption_house_fax;

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

    public function getCanPublishCount(): ?int
    {
        return $this->can_publish_count;
    }

    public function setCanPublishCount(?int $can_publish_count): self
    {
        $this->can_publish_count = $can_publish_count;

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

    public function getCageSize(): ?int
    {
        return $this->cage_size;
    }

    public function setCageSize(?int $cage_size): self
    {
        $this->cage_size = $cage_size;

        return $this;
    }

    public function getAdoptionExpYear(): ?int
    {
        return $this->adoption_exp_year;
    }

    public function setAdoptionExpYear(?int $adoption_exp_year): self
    {
        $this->adoption_exp_year = $adoption_exp_year;

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

    /**
     * Set status.
     *
     * @param \Eccube\Entity\Master\CustomerStatus|null $status
     *
     * @return Customer
     */
    public function setStatus(\Eccube\Entity\Master\CustomerStatus $status = null)
    {
        $this->Status = $status;

        return $this;
    }

    /**
     * Get status.
     *
     * @return \Eccube\Entity\Master\CustomerStatus|null
     */
    public function getStatus()
    {
        return $this->Status;
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
        return ['ROLE_USER'];
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

    public function getDiscriminatorType(): ?string
    {
        return $this->discriminator_type;
    }

    public function setDiscriminatorType(?string $discriminator_type): self
    {
        $this->discriminator_type = $discriminator_type;

        return $this;
    }

}
