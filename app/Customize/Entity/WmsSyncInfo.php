<?php

namespace Customize\Entity;

use Customize\Repository\WmsSyncInfoRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="ald_wms_sync_info")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discriminator_type", type="string", length=255)
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Entity(repositoryClass=WmsSyncInfoRepository::class)
 */
class WmsSyncInfo
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id", type="integer")
     */
    private $id;

    /**
     * @ORM\Column(name="sync_action", type="smallint", nullable=true)
     */
    private $sync_action;

    /**
     * @ORM\Column(name="sync_date", type="datetime", nullable=false)
     */
    private $sync_date;

    /**
     * @ORM\Column(name="sync_result", type="smallint", nullable=false)
     */
    private $sync_result;

    /**
     * @ORM\Column(name="sync_log", type="text", nullable=true)
     */
    private $sync_log;

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

    public function getSyncAction(): ?int
    {
        return $this->sync_action;
    }

    public function setSyncAction(int $sync_action): self
    {
        $this->sync_action = $sync_action;

        return $this;
    }

    public function getSyncDate(): ?\DateTimeInterface
    {
        return $this->sync_date;
    }

    public function setSyncDate(\DateTimeInterface $sync_date): self
    {
        $this->sync_date = $sync_date;

        return $this;
    }

    public function getSyncResult(): ?int
    {
        return $this->sync_result;
    }

    public function setSyncResult(int $sync_result): self
    {
        $this->sync_result = $sync_result;

        return $this;
    }

    public function getSyncLog(): ?string
    {
        return $this->sync_log;
    }

    public function setSyncLog(?string $sync_log): self
    {
        $this->sync_log = $sync_log;

        return $this;
    }

    /**
     * Set createDate.
     *
     * @param \DateTime $createDate
     *
     * @return self
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
     * @return self
     */
    public function setUpdateDate($updateDate)
    {
        $this->update_date = $updateDate;

        return $this;
    }
}
