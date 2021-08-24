<?php

namespace Customize\Entity;

use Customize\Repository\SendoffReasonRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * @ORM\Table(name="alm_sendoff_reasons")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discriminator_type", type="string", length=255)
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Entity(repositoryClass=SendoffReasonRepository::class)
 */
class SendoffReason
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(name="id", type="integer")
     */
    private $id;

    /**
     * @ORM\Column(name="reason", type="string", length=255)
     */
    private $reason;

    /**
     * @ORM\Column(name="is_adoption_visible", type="smallint", options={"default" = 0})
     */
    private $is_adoption_visible = 0;

    /**
     * @ORM\Column(name="is_breeder_visible", type="smallint", options={"default" = 0})
     */
    private $is_breeder_visible = 0;

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

    /**
     * @ORM\OneToMany(targetEntity="Customize\Entity\ConservationContacts", mappedBy="sendoffReason")
     */
    private $conservationContacts;

    /**
     * @ORM\OneToMany(targetEntity="Customize\Entity\BreederContacts", mappedBy="sendoffReason")
     */
    private $breederContacts;

    public function __construct()
    {
        $this->conservationContacts = new ArrayCollection();
        $this->breederContacts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function setReason(string $reason): self
    {
        $this->reason = $reason;

        return $this;
    }

    public function getIsAdoptionVisible(): ?int
    {
        return $this->is_adoption_visible;
    }

    public function setIsAdoptionVisible(int $is_adoption_visible): self
    {
        $this->is_adoption_visible = $is_adoption_visible;

        return $this;
    }

    public function getIsBreederVisible(): ?int
    {
        return $this->is_breeder_visible;
    }

    public function setIsBreederVisible(int $is_breeder_visible): self
    {
        $this->is_breeder_visible = $is_breeder_visible;

        return $this;
    }

    public function getConservationContacts(): Collection
    {
        return $this->conservationContacts;
    }

    public function getBreederContacts(): Collection
    {
        return $this->breederContacts;
    }
}
