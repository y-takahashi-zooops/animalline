<?php

namespace Customize\Entity;

use Customize\Repository\BreederContactsRepository;
use Doctrine\ORM\Mapping as ORM;
use Eccube\Entity\Customer;

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
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Customer::class, inversedBy="breederContacts")
     * @ORM\JoinColumn(name="conservation_id", referencedColumnName="id", nullable=false)
     */
    private $customer_id;

    /**
     * @ORM\ManyToOne(targetEntity=Breeders::class, inversedBy="breederContacts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $breeder_id;

    /**
     * @ORM\Column(type="smallint")
     */
    private $message_from;

    /**
     * @ORM\ManyToOne(targetEntity=BreederPets::class, inversedBy="breederContacts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $pet_id;

    /**
     * @ORM\Column(type="smallint")
     */
    private $contact_type;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $contact_title;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $contact_description;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $booking_request;

    /**
     * @ORM\Column(type="integer", options={"default" = 0})
     */
    private $parent_message_id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $send_date;

    /**
     * @ORM\Column(type="smallint", options={"default" = 0})
     */
    private $is_response;

    /**
     * @ORM\Column(type="smallint", options={"default" = 0})
     */
    private $contract_status;

    /**
     * @ORM\ManyToOne(targetEntity=SendoffReason::class, inversedBy="conservationContacts")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="reason", referencedColumnName="id", nullable=true)
     * })
     */
    private $reason;

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

    public function getCustomerId(): ?Customer
    {
        return $this->customer_id;
    }

    public function setCustomerId(?Customer $customer_id): self
    {
        $this->customer_id = $customer_id;

        return $this;
    }

    public function getBreederId(): ?Breeders
    {
        return $this->breeder_id;
    }

    public function setBreederId(?Breeders $breeder_id): self
    {
        $this->breeder_id = $breeder_id;

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

    public function getPetId(): ?BreederPets
    {
        return $this->pet_id;
    }

    public function setPetId(?BreederPets $pet_id): self
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
        return $this->reason;
    }

    public function setReason(?SendoffReason $reason): self
    {
        $this->reason = $reason;

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
