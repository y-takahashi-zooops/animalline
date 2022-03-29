<?php

namespace Customize\Entity;

use Customize\Repository\MonthlyInvoiceRepository;
use Doctrine\ORM\Mapping as ORM;
use Eccube\Entity\Customer;

/**
 * @ORM\Entity(repositoryClass=MonthlyInvoiceRepository::class)
 */
/**
 * @ORM\Table(name="ald_monthly_invoice")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discriminator_type", type="string", length=255)
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Entity(repositoryClass=MonthlyInvoiceRepository::class)
 */
class MonthlyInvoice
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id", type="integer")
     */
    private $id;

    /**
     * @ORM\Column(name="site_category", type="smallint", nullable=true)
     */
    private $site_category;

    /**
     * @ORM\ManyToOne(targetEntity=Customer::class)
     * @ORM\JoinColumn(name="customer_id", nullable=true)
     */
    private $Customer;

    /**
     * @ORM\Column(name="yearmonth", type="string", length=6, nullable=true)
     */
    private $yearmonth;

    /**
     * @ORM\Column(name="contract_count", type="integer", nullable=true)
     */
    private $contract_count;

    /**
     * @ORM\Column(name="contract_commission", type="integer", nullable=true)
     */
    private $contract_commission;

    /**
     * @ORM\Column(name="ec_count", type="integer", nullable=true)
     */
    private $ec_count;

    /**
     * @ORM\Column(name="ec_incentive", type="integer", nullable=true)
     */
    private $ec_incentive;

    /**
     * @ORM\Column(name="total_incentive", type="integer", nullable=true)
     */
    private $total_incentive;

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

    public function getSiteCategory(): ?int
    {
        return $this->site_category;
    }

    public function setSiteCategory(?int $site_category): self
    {
        $this->site_category = $site_category;

        return $this;
    }

    public function getCustomer(): ?Customer
    {
        return $this->Customer;
    }

    public function setCustomer(?Customer $Customer): self
    {
        $this->Customer = $Customer;

        return $this;
    }

    public function getYearmonth(): ?string
    {
        return $this->yearmonth;
    }

    public function setYearmonth(?string $yearmonth): self
    {
        $this->yearmonth = $yearmonth;

        return $this;
    }

    public function getContractCount(): ?int
    {
        return $this->contract_count;
    }

    public function setContractCount(?int $contract_count): self
    {
        $this->contract_count = $contract_count;

        return $this;
    }

    public function getContractCommission(): ?int
    {
        return $this->contract_commission;
    }

    public function setContractCommission(?int $contract_commission): self
    {
        $this->contract_commission = $contract_commission;

        return $this;
    }

    public function getEcCount(): ?int
    {
        return $this->ec_count;
    }

    public function setEcCount(?int $ec_count): self
    {
        $this->ec_count = $ec_count;

        return $this;
    }

    public function getEcIncentive(): ?int
    {
        return $this->ec_incentive;
    }

    public function setEcIncentive(?int $ec_incentive): self
    {
        $this->ec_incentive = $ec_incentive;

        return $this;
    }

    public function getTotalIncentive(): ?int
    {
        return $this->total_incentive;
    }

    public function setTotalIncentive(?int $total_incentive): self
    {
        $this->total_incentive = $total_incentive;

        return $this;
    }

    /**
     * Set createDate.
     *
     * @param \DateTime $createDate
     *
     * @return MonthlyInvoice
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
     * @return MonthlyInvoice
     */
    public function setUpdateDate($updateDate)
    {
        $this->update_date = $updateDate;

        return $this;
    }
}
