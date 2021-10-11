<?php

namespace Customize\Entity;

use Customize\Repository\DnaCheckStatusDetailRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="ald_dna_check_status_detail")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discriminator_type", type="string", length=255)
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Entity(repositoryClass=DnaCheckStatusDetailRepository::class)
 */
class DnaCheckStatusDetail
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id", type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=DnaCheckStatus::class, inversedBy="CheckStatusDetails")
     * @ORM\JoinColumn(name="check_status_id", nullable=false)
     */
    private $CheckStatus;

    /**
     * @ORM\ManyToOne(targetEntity=DnaCheckKinds::class, inversedBy="CheckStatusDetails")
     * @ORM\JoinColumn(name="check_kinds_id", nullable=true)
     */
    private $CheckKinds;

    /**
     * @ORM\Column(name="check_result", type="smallint", nullable=true)
     */
    private $check_result;

    /**
     * @ORM\Column(name="create_date", type="datetime", nullable=true)
     */
    private $create_date;

    /**
     * @ORM\Column(name="update_date", type="datetime", nullable=true)
     */
    private $update_date;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCheckStatus(): ?DnaCheckStatus
    {
        return $this->CheckStatus;
    }

    public function setCheckStatus(?DnaCheckStatus $CheckStatus): self
    {
        $this->CheckStatus = $CheckStatus;

        return $this;
    }

    public function getCheckKinds(): ?DnaCheckKinds
    {
        return $this->CheckKinds;
    }

    public function setCheckKinds(?DnaCheckKinds $CheckKinds): self
    {
        $this->CheckKinds = $CheckKinds;

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

    public function getCreateDate(): ?\DateTime
    {
        return $this->create_date;
    }

    public function setCreateDate(\DateTime $create_date): self
    {
        $this->create_date = $create_date;

        return $this;
    }

    public function getUpdateDate(): ?\DateTime
    {
        return $this->update_date;
    }

    public function setUpdateDate(\DateTime $update_date): self
    {
        $this->update_date = $update_date;

        return $this;
    }
}
