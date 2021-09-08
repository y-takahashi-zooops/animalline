<?php

namespace Customize\Entity;

use Customize\Repository\ConservationContactsRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ConservationContactsRepository::class)
 * @ORM\Table(name="ald_conservation_contacts")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discriminator_type", type="string", length=255)
 * @ORM\HasLifecycleCallbacks()
 */
class ConservationContacts
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(name="id", type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=ConservationContactHeader::class, inversedBy="ConservationContacts")
     * @ORM\JoinColumn(name="header_id", nullable=false)
     */
    private $ConservationHeader;

    /**
     * @ORM\Column(name="message_from", type="smallint", nullable=false)
     */
    private $message_from;

    /**
     * @ORM\Column(name="contact_description", type="text", nullable=true)
     */
    private $contact_description;

    /**
     * @ORM\Column(name="send_date", type="datetime", nullable=false)
     */
    private $send_date;

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

    public function getConservationHeader(): ?ConservationContactHeader
    {
        return $this->ConservationHeader;
    }

    public function setConservationHeader(?ConservationContactHeader $ConservationHeader): self
    {
        $this->ConservationHeader = $ConservationHeader;

        return $this;
    }

    public function getMessageFrom(): ?int
    {
        return $this->message_from;
    }

    public function setMessageFrom(int $message_from): self
    {
        $this->message_from = $message_from;

        return $this;
    }

    public function getContactDescription(): ?string
    {
        return $this->contact_description;
    }

    public function setContactDescription(?string $contact_description): self
    {
        $this->contact_description = $contact_description;

        return $this;
    }

    public function getSendDate(): ?\DateTimeInterface
    {
        return $this->send_date;
    }

    public function setSendDate(\DateTimeInterface $send_date): self
    {
        $this->send_date = $send_date;

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
