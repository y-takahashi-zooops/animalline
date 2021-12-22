<?php

namespace Customize\Entity;

use Customize\Repository\BankAccountRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="alm_conservation_bank_account")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discriminator_type", type="string", length=255)
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Entity(repositoryClass=BankAccountRepository::class)
 */
class ConservationBankAccount
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(name="id", type="integer")

     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Conservations::class, inversedBy="BankAccounts")
     * @ORM\JoinColumn(name="conservation_id", referencedColumnName="id", nullable=false)
     */
    private $Conservation;

    /**
     * @ORM\Column(name="bank_name", type="string", length=40, nullable=false)
     */
    private $bank_name;

    /**
     * @ORM\Column(name="bank_code", type="string", length=4, nullable=false)
     */
    private $bank_code;

    /**
     * @ORM\Column(name="branch_name", type="string", length=40, nullable=false)
     */
    private $branch_name;

    /**
     * @ORM\Column(name="branch_number", type="string", length=3, nullable=false)
     */
    private $branch_number;

    /**
     * @ORM\Column(name="account_number", type="string", length=7, nullable=false)
     */
    private $account_number;

    /**
     * @ORM\Column(name="account_kind", type="smallint", nullable=false)
     */
    private $account_kind;

    /**
     * @ORM\Column(name="name", type="string", length=40, nullable=false)
     */
    private $name;

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

    public function setConservation(Conservations $Conservation): self
    {
        $this->Conservation = $Conservation;

        return $this;
    }

    public function getBankName(): ?string
    {
        return $this->bank_name;
    }

    public function setBankName(?string $bank_name): self
    {
        $this->bank_name = $bank_name;

        return $this;
    }

    public function getBankCode(): ?string
    {
        return $this->bank_code;
    }

    public function setBankCode(?string $bank_code): self
    {
        $this->bank_code = $bank_code;

        return $this;
    }

    public function getBranchName(): ?string
    {
        return $this->branch_name;
    }

    public function setBranchName(?string $branch_name): self
    {
        $this->branch_name = $branch_name;

        return $this;
    }

    public function getBranchNumber(): ?string
    {
        return $this->branch_number;
    }

    public function setBranchNumber(?string $branch_number): self
    {
        $this->branch_number = $branch_number;

        return $this;
    }

    public function getAccountNumber(): ?string
    {
        return $this->account_number;
    }

    public function setAccountNumber(?string $account_number): self
    {
        $this->account_number = $account_number;

        return $this;
    }

    public function getAccountKind(): ?int
    {
        return $this->account_kind;
    }

    public function setAccountKind(?int $account_kind): self
    {
        $this->account_kind = $account_kind;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Set createDate.
     *
     * @param \DateTime $createDate
     *
     * @return BankAccount
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
     * @return BankAccount
     */
    public function setUpdateDate($updateDate)
    {
        $this->update_date = $updateDate;

        return $this;
    }
}
