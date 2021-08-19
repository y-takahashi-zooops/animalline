<?php

namespace Customize\Entity;

use Customize\Repository\PrefAdjacentRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PrefAdjacentRepository::class)
 * @ORM\Table(name="alm_pref_adjacent")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discriminator_type", type="string", length=255)
 * @ORM\HasLifecycleCallbacks()
 */
class PrefAdjacent
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(name="pref_id", type="smallint", nullable=false)
     */
    private $pref_id;

    /**
     * @ORM\Column(name="adjacent_pref_id", type="smallint", nullable=false)
     */
    private $adjacent_pref_id;

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

    public function getPrefId(): ?int
    {
        return $this->pref_id;
    }

    public function setPrefId(?int $pref_id): self
    {
        $this->pref_id = $pref_id;

        return $this;
    }

    public function getAdjacentPrefId(): ?int
    {
        return $this->adjacent_pref_id;
    }

    public function setAdjacentPrefId(?int $adjacent_pref_id): self
    {
        $this->adjacent_pref_id = $adjacent_pref_id;

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
