<?php

namespace Customize\Entity;

use Customize\Repository\BreederExaminationInfoRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=BreederExaminationInfoRepository::class)
 * @ORM\Table(name="ald_breeder_examination_info")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discriminator_type", type="string", length=255)
 * @ORM\HasLifecycleCallbacks()
 */
class BreederExaminationInfo
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Breeders::class, inversedBy="breederExaminationInfos")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="breeder_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $Breeder;

    /**
     * @ORM\Column(type="smallint")
     */
    private $pet_type;


    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $pedigree_organization_other;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $pedigree_organization;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $breeding_pet_count;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $parent_pet_count_1;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $parent_pet_count_2;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $parent_pet_count_3;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $parent_pet_buy_place_1;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $parent_pet_buy_place_2;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $parent_pet_buy_place_3;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $owner_worktime_ave;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $family_staff_count;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $family_staff_worktime_ave;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $fulltime_staff_count;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $fulltime_staff_worktime_ave;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $parttime_staff_count;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $parttime_staff_worktime_ave;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $other_staff_count;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $other_staff_worktime_ave;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $breeding_exp_year;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $breeding_exp_month;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $is_participate_show;

    /**
     * @ORM\Column(type="smallint", options={"default" = 0})
     */
    private $cage_size_1;

    /**
     * @ORM\Column(type="smallint", options={"default" = 0})
     */
    private $cage_size_2;

    /**
     * @ORM\Column(type="smallint", options={"default" = 0})
     */
    private $cage_size_3;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $cage_size_other;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $exercise_status;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $exercise_status_other;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $is_now_publising;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $publish_pet_count;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $breeding_experience;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $selling_experience;

    /**
     * @ORM\Column(type="smallint", options={"default" = 0})
     */
    private $input_status;

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

    public function setBreeder(?Breeders $Breeder): self
    {
        $this->Breeder = $Breeder;

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

    public function getPedigreeOrganizationOther(): ?string
    {
        return $this->pedigree_organization_other;
    }

    public function setPedigreeOrganizationOther(?string $pedigree_organization_other): self
    {
        $this->pedigree_organization_other = $pedigree_organization_other;

        return $this;
    }

    public function getBreedingPetCount(): ?int
    {
        return $this->breeding_pet_count;
    }

    public function setBreedingPetCount(?int $breeding_pet_count): self
    {
        $this->breeding_pet_count = $breeding_pet_count;

        return $this;
    }

    public function getParentPetCount1(): ?int
    {
        return $this->parent_pet_count_1;
    }

    public function setParentPetCount1(?int $parent_pet_count_1): self
    {
        $this->parent_pet_count_1 = $parent_pet_count_1;

        return $this;
    }

    public function getPedigreeOrganization(): ?int
    {
        return $this->pedigree_organization;
    }

    public function setPedigreeOrganization(?int $pedigree_organization): self
    {
        $this->pedigree_organization = $pedigree_organization;

        return $this;
    }

    public function getParentPetCount2(): ?string
    {
        return $this->parent_pet_count_2;
    }

    public function setParentPetCount2(?string $parent_pet_count_2): self
    {
        $this->parent_pet_count_2 = $parent_pet_count_2;

        return $this;
    }

    public function getParentPetCount3(): ?int
    {
        return $this->parent_pet_count_3;
    }

    public function setParentPetCount3(?int $parent_pet_count_3): self
    {
        $this->parent_pet_count_3 = $parent_pet_count_3;

        return $this;
    }

    public function getParentPetBuyPlace1(): ?int
    {
        return $this->parent_pet_buy_place_1;
    }

    public function setParentPetBuyPlace1(?int $parent_pet_buy_place_1): self
    {
        $this->parent_pet_buy_place_1 = $parent_pet_buy_place_1;

        return $this;
    }

    public function getParentPetBuyPlace2(): ?int
    {
        return $this->parent_pet_buy_place_2;
    }

    public function setParentPetBuyPlace2(?int $parent_pet_buy_place_2): self
    {
        $this->parent_pet_buy_place_2 = $parent_pet_buy_place_2;

        return $this;
    }

    public function getParentPetBuyPlace3(): ?int
    {
        return $this->parent_pet_buy_place_3;
    }

    public function setParentPetBuyPlace3(?int $parent_pet_buy_place_3): self
    {
        $this->parent_pet_buy_place_3 = $parent_pet_buy_place_3;

        return $this;
    }

    public function getOwnerWorktimeAve(): ?int
    {
        return $this->owner_worktime_ave;
    }

    public function setOwnerWorktimeAve(?int $owner_worktime_ave): self
    {
        $this->owner_worktime_ave = $owner_worktime_ave;

        return $this;
    }

    public function getFamilyStaffCount(): ?int
    {
        return $this->family_staff_count;
    }

    public function setFamilyStaffCount(?int $family_staff_count): self
    {
        $this->family_staff_count = $family_staff_count;

        return $this;
    }

    public function getFamilyStaffWorktimeAve(): ?int
    {
        return $this->family_staff_worktime_ave;
    }

    public function setFamilyStaffWorktimeAve(?int $family_staff_worktime_ave): self
    {
        $this->family_staff_worktime_ave = $family_staff_worktime_ave;

        return $this;
    }

    public function getFulltimeStaffCount(): ?int
    {
        return $this->fulltime_staff_count;
    }

    public function setFulltimeStaffCount(int $fulltime_staff_count): self
    {
        $this->fulltime_staff_count = $fulltime_staff_count;

        return $this;
    }

    public function getFulltimeStaffWorktimeAve(): ?int
    {
        return $this->fulltime_staff_worktime_ave;
    }

    public function setFulltimeStaffWorktimeAve(int $fulltime_staff_worktime_ave): self
    {
        $this->fulltime_staff_worktime_ave = $fulltime_staff_worktime_ave;

        return $this;
    }

    public function getParttimeStaffCount(): ?int
    {
        return $this->parttime_staff_count;
    }

    public function setParttimeStaffCount(?int $parttime_staff_count): self
    {
        $this->parttime_staff_count = $parttime_staff_count;

        return $this;
    }

    public function getParttimeStaffWorktimeAve(): ?int
    {
        return $this->parttime_staff_worktime_ave;
    }

    public function setParttimeStaffWorktimeAve(?int $parttime_staff_worktime_ave): self
    {
        $this->parttime_staff_worktime_ave = $parttime_staff_worktime_ave;

        return $this;
    }

    public function getOtherStaffCount(): ?int
    {
        return $this->other_staff_count;
    }

    public function setOtherStaffCount(?int $other_staff_count): self
    {
        $this->other_staff_count = $other_staff_count;

        return $this;
    }

    public function getOtherStaffWorktimeAve(): ?int
    {
        return $this->other_staff_worktime_ave;
    }

    public function setOtherStaffWorktimeAve(?int $other_staff_worktime_ave): self
    {
        $this->other_staff_worktime_ave = $other_staff_worktime_ave;

        return $this;
    }

    public function getBreedingExpYear(): ?int
    {
        return $this->breeding_exp_year;
    }

    public function setBreedingExpYear(int $breeding_exp_year): self
    {
        $this->breeding_exp_year = $breeding_exp_year;

        return $this;
    }

    public function getBreedingExpMonth(): ?int
    {
        return $this->breeding_exp_month;
    }

    public function setBreedingExpMonth(?int $breeding_exp_month): self
    {
        $this->breeding_exp_month = $breeding_exp_month;

        return $this;
    }

    public function getIsParticipateShow(): ?int
    {
        return $this->is_participate_show;
    }

    public function setIsParticipateShow(int $is_participate_show): self
    {
        $this->is_participate_show = $is_participate_show;

        return $this;
    }

    public function getCageSize1(): ?int
    {
        return $this->cage_size_1;
    }

    public function setCageSize1(?int $cage_size_1): self
    {
        $this->cage_size_1 = $cage_size_1;

        return $this;
    }

    public function getCageSize2(): ?int
    {
        return $this->cage_size_2;
    }

    public function setCageSize2(?int $cage_size_2): self
    {
        $this->cage_size_2 = $cage_size_2;

        return $this;
    }

    public function getCageSize3(): ?int
    {
        return $this->cage_size_3;
    }

    public function setCageSize3(int $cage_size_3): self
    {
        $this->cage_size_3 = $cage_size_3;

        return $this;
    }

    public function getCageSizeOther(): ?string
    {
        return $this->cage_size_other;
    }

    public function setCageSizeOther(?string $cage_size_other): self
    {
        $this->cage_size_other = $cage_size_other;

        return $this;
    }

    public function getExerciseStatus(): ?int
    {
        return $this->exercise_status;
    }

    public function setExerciseStatus(?int $exercise_status): self
    {
        $this->exercise_status = $exercise_status;

        return $this;
    }

    public function getExerciseStatusOther(): ?string
    {
        return $this->exercise_status_other;
    }

    public function setExerciseStatusOther(?string $exercise_status_other): self
    {
        $this->exercise_status_other = $exercise_status_other;

        return $this;
    }

    public function getIsNowPublising(): ?int
    {
        return $this->is_now_publising;
    }

    public function setIsNowPublising(?int $is_now_publising): self
    {
        $this->is_now_publising = $is_now_publising;

        return $this;
    }

    public function getPublishPetCount(): ?int
    {
        return $this->publish_pet_count;
    }

    public function setPublishPetCount(?int $publish_pet_count): self
    {
        $this->publish_pet_count = $publish_pet_count;

        return $this;
    }

    public function getBreedingExperience(): ?int
    {
        return $this->breeding_experience;
    }

    public function setBreedingExperience(?int $breeding_experience): self
    {
        $this->breeding_experience = $breeding_experience;

        return $this;
    }

    public function getSellingExperience(): ?int
    {
        return $this->selling_experience;
    }

    public function setSellingExperience(?int $selling_experience): self
    {
        $this->selling_experience = $selling_experience;

        return $this;
    }

    public function getInputStatus(): ?int
    {
        return $this->input_status;
    }

    public function setInputStatus(int $input_status): self
    {
        $this->input_status = $input_status;

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
}
