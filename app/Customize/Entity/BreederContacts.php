<?php

namespace Customize\Entity;

use Customize\Repository\BreederContactsRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=BreederContactsRepository::class)
 * @ORM\Table(name="ald_breeder_contacts")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discriminator_type", type="string", length=255)
 * @ORM\HasLifecycleCallbacks()
 */
class BreederContacts
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(name="id", type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=BreederContactHeader::class, inversedBy="BreederContacts")
     * @ORM\JoinColumn(name="header_id", nullable=false)
     */
    private $BreederContactHeader;

    /**
     * @ORM\Column(name="message_from", type="smallint")
     */
    private $message_from;

    /**
     * @ORM\Column(name="contact_description", type="string", length=255, nullable=true)
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

    /**
     * @ORM\Column(name="is_reading", type="integer", options={"default" = 0}, nullable=false)
     */
    private $is_reading;

    /**
     * @ORM\Column(name="is_delete", type="smallint", nullable=false, options={"default" = 0})
     */
    private $is_delete = 0;

    /**
     * @ORM\Column(name="image_file", type="string", length=255, nullable=true)
     */
    private $image_file;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBreederContactHeader(): ?BreederContactHeader
    {
        return $this->BreederContactHeader;
    }

    public function setBreederContactHeader(BreederContactHeader $BreederContactHeader): self
    {
        $this->BreederContactHeader = $BreederContactHeader;

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

    public function getImageFile(): ?string
    {
        return $this->image_file;
    }

    public function setImageFile(?string $image_file): self
    {
        $this->image_file = $image_file;

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

    public function getIsReading(): ?int
    {
        return $this->is_reading;
    }

    public function setIsReading(int $is_reading): self
    {
        $this->is_reading = $is_reading;

        return $this;
    }

    public function getIsDelete(): ?int
    {
        return $this->is_delete;
    }

    public function setIsDelete(int $is_delete): self
    {
        $this->is_delete = $is_delete;

        return $this;
    }
}
