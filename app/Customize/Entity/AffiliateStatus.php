<?php

namespace Customize\Entity;

use Customize\Repository\AffiliateStatusRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="ald_affiliate_status")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discriminator_type", type="string", length=255)
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Entity(repositoryClass=AffiliateStatusRepository::class)
 */
class AffiliateStatus
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(name="id", type="integer")

     */
    private $id;

    /**
     * @ORM\Column(name="affiliate_key", type="string", length=255)
     */
    private $affiliate_key;

    /**
     * @ORM\Column(name="campaign_id", type="smallint")
     */
    private $campaign_id;

    /**
     * @ORM\Column(name="session_id", type="string", length=255)
     */
    private $session_id;

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

    public function getAffiliateKey(): ?string
    {
        return $this->affiliate_key;
    }

    public function setAffiliateKey(string $affiliate_key): self
    {
        $this->affiliate_key = $affiliate_key;

        return $this;
    }

    public function getCampaignId(): ?int
    {
        return $this->campaign_id;
    }

    public function setCampaignId(int $campaign_id): self
    {
        $this->campaign_id = $campaign_id;

        return $this;
    }

    public function getSessionId(): ?string
    {
        return $this->session_id;
    }

    public function setSessionId(string $session_id): self
    {
        $this->session_id = $session_id;

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
