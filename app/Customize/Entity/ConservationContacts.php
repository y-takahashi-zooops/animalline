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
     * @ORM\GeneratedValue
     * @ORM\Column(name="id", type="integer")
     */
    private $id;

    /**
     * @var \Eccube\Entity\Customer
     *
     * @ORM\ManyToOne(targetEntity="Eccube\Entity\Customer")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="customer_id", referencedColumnName="id")
     * })
     */
    private $Customer;

    /**
     * @ORM\ManyToOne(targetEntity=Conservations::class, inversedBy="conservationContacts")
     * @ORM\JoinColumn(name="conservation_id", nullable=false)
     */
    private $conservation_id;

    /**
     * @ORM\Column(name="message_from", type="smallint")
     */
    private $message_from;

    /**
     * @ORM\Column(name="pet_id", type="integer")
     */
    private $pet_id;

    /**
     * @ORM\Column(name="contact_type", type="smallint")
     */
    private $contact_type;

    /**
     * @ORM\Column(name="contact_title", type="string", length=255, nullable=true)
     */
    private $contact_title;

    /**
     * @ORM\Column(name="contact_description", type="text", nullable=true)
     */
    private $contact_description;

    /**
     * @ORM\Column(name="booking_request", type="text", nullable=true)
     */
    private $booking_request;

    /**
     * @ORM\Column(name="parent_message_id", type="integer", options={"default" = 0})
     */
    private $parent_message_id;

    /**
     * @ORM\Column(name="send_date", type="datetime")
     */
    private $send_date;

    /**
     * @ORM\Column(name="is_responce", type="smallint", options={"default" = 0})
     */
    private $is_responce;

    /**
     * @ORM\Column(name="contract_status", type="smallint", options={"default" = 0})
     */
    private $contract_status;

    /**
     * @ORM\Column(name="reason", type="smallint", options={"default" = 0})
     */
    private $reason;

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Set customer.
     *
     * @param \Eccube\Entity\Customer|null $customer
     *
     * @return ConservationContacts
     */
    public function setCustomer(\Eccube\Entity\Customer $customer = null)
    {
        $this->Customer = $customer;

        return $this;
    }

    /**
     * Get customer.
     *
     * @return \Eccube\Entity\Customer|null
     */
    public function getCustomer()
    {
        return $this->Customer;
    }

    public function getConservationId(): ?Conservations
    {
        return $this->conservation_id;
    }

    public function setConservationId(?Conservations $conservation_id): self
    {
        $this->conservation_id = $conservation_id;

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

    public function getPetId(): ?int
    {
        return $this->pet_id;
    }

    public function setPetId(int $pet_id): self
    {
        $this->pet_id = $pet_id;

        return $this;
    }

    public function getContactType(): ?int
    {
        return $this->contact_type;
    }

    public function setContactType(int $contact_type): self
    {
        $this->contact_type = $contact_type;

        return $this;
    }

    public function getContactTitle(): ?string
    {
        return $this->contact_title;
    }

    public function setContactTitle(?string $contact_title): self
    {
        $this->contact_title = $contact_title;

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

    public function getBookingRequest(): ?string
    {
        return $this->booking_request;
    }

    public function setBookingRequest(?string $booking_request): self
    {
        $this->booking_request = $booking_request;

        return $this;
    }

    public function getParentMessageId(): ?int
    {
        return $this->parent_message_id;
    }

    public function setParentMessageId(int $parent_message_id): self
    {
        $this->parent_message_id = $parent_message_id;

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

    public function getIsResponce(): ?int
    {
        return $this->is_responce;
    }

    public function setIsResponce(int $is_responce): self
    {
        $this->is_responce = $is_responce;

        return $this;
    }

    public function getContractStatus(): ?int
    {
        return $this->contract_status;
    }

    public function setContractStatus(int $contract_status): self
    {
        $this->contract_status = $contract_status;

        return $this;
    }

    public function getReason(): ?int
    {
        return $this->reason;
    }

    public function setReason(int $reason): self
    {
        $this->reason = $reason;

        return $this;
    }
}
