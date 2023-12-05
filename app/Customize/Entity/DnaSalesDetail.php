<?php

namespace Customize\Entity;

use Customize\Repository\DnaSalesDetailRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="ald_dna_sales_detail")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\Entity(repositoryClass=DnaSalesDetailRepository::class)
 * @ORM\DiscriminatorColumn(name="discriminator_type", type="string", length=255)
 * @ORM\HasLifecycleCallbacks()
 */
class DnaSalesDetail
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=DnaSalesStatus::class, inversedBy="DnaSalesDetail")
     * @ORM\JoinColumn(name="check_status_id", nullable=false)
     */
    private $DnaSalesStatus;

    /**
     * @ORM\ManyToOne(targetEntity=DnaCheckKindsEc::class, inversedBy="DnaSalesDetail")
     * @ORM\JoinColumn(name="alm_dna_check_kinds_id", nullable=false)
     */
    private $dnaCheckKind;

    /**
     * @ORM\Column(type="integer")
     */
    private $alm_dna_check_kinds_id;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $check_result;

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

    public function getDnaCheckKind()
    {
        return $this->dnaCheckKind;
    }

    public function setDnaCheckKind(?DnaSalesStatus $dnaCheckKind)
    {
        $this->dnaCheckKind = $dnaCheckKind;

        return $this;
    }

    public function getDnaSalesStatus(): ?DnaSalesStatus
    {
        return $this->DnaSalesStatus;
    }

    public function setDnaSalesStatus(?DnaSalesStatus $DnaSalesStatus): self
    {
        $this->DnaSalesStatus = $DnaSalesStatus;

        return $this;
    }

    public function getAlmDnaCheckKindsId(): ?int
    {
        return $this->alm_dna_check_kinds_id;
    }

    public function setAlmDnaCheckKindsId(int $alm_dna_check_kinds_id): self
    {
        $this->alm_dna_check_kinds_id = $alm_dna_check_kinds_id;

        return $this;
    }

    public function getCheckResult(): ?int
    {
        return $this->check_result;
    }

    public function setCheckResult(?int $check_result): self
    {
        $this->check_result = $check_result;

        return $this;
    }

    public function getCreateDate(): ?\DateTimeInterface
    {
        return $this->create_date;
    }

    /**
     * Set createDate.
     *
     * @param ?\DateTime $createDate
     *
     * @return self
     */
    public function setCreateDate(?\DateTime $createDate): self
    {
        $this->create_date = $createDate;

        return $this;
    }

    public function getUpdateDate(): ?\DateTimeInterface
    {
        return $this->update_date;
    }

    /**
     * Set updateDate.
     *
     * @param ?\DateTime $updateDate
     *
     * @return self
     */
    public function setUpdateDate(?\DateTime $updateDate): self
    {
        $this->update_date = $updateDate;

        return $this;
    }
}
