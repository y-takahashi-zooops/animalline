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
     *   @ORM\JoinColumn(name="customer_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $Customer;

    /**
     * @ORM\ManyToOne(targetEntity=Conservations::class, inversedBy="ConservationContacts")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="conservation_id", referencedColumnName="id", columnDefinition="INT DEFAULT 0 NOT NULL")
     * })
     */
    private $Conservation;

    /**
     * @ORM\Column(name="message_from", type="smallint")
     */
    private $message_from;

    /**
     * @ORM\ManyToOne(targetEntity=ConservationPets::class)
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="pet_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $Pet;

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
     * @ORM\Column(name="is_response", type="smallint", options={"default" = 0})
     */
    private $is_response;

    /**
     * @ORM\Column(name="contract_status", type="smallint", options={"default" = 0}, nullable=false)
     */
    private $contract_status = 0;

    /**
     * @ORM\ManyToOne(targetEntity=SendoffReason::class, inversedBy="ConservationContacts")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="reason", referencedColumnName="id", nullable=true)
     * })
     */
    private $Reason;

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

    public function getConservation(): ?Conservations
    {
        return $this->Conservation;
    }

    public function setConservation(?Conservations $conservation): self
    {
        $this->Conservation = $conservation;

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

    public function getPet(): ?ConservationPets
    {
        return $this->Pet;
    }

    public function setPet(?ConservationPets $pet): self
    {
        $this->Pet = $pet;

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

    public function getIsResponse(): ?int
    {
        return $this->is_response;
    }

    public function setIsResponse(int $is_response): self
    {
        $this->is_response = $is_response;

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

    public function getReason(): ?SendoffReason
    {
        return $this->Reason;
    }

    public function setReason(?SendoffReason $Reason): self
    {
        $this->Reason = $Reason;

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
