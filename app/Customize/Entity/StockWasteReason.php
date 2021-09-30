<?php

namespace Customize\Entity;

use Customize\Repository\StockWasteReasonRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="alm_stock_waste_reason")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discriminator_type", type="string", length=255)
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Entity(repositoryClass=StockWasteReasonRepository::class)
 */
class StockWasteReason
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(name="id", type="integer")
     */
    private $id;

    /**
     * @ORM\Column(name="waste_reason", type="string", length=255, nullable=true)
     */
    private $waste_reason;

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

    public function getWasteReason(): ?string
    {
        return $this->waste_reason;
    }

    public function setWasteReason(?string $waste_reason): self
    {
        $this->waste_reason = $waste_reason;

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
