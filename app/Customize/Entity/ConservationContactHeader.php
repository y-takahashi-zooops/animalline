<?php

namespace Customize\Entity;

use Customize\Repository\ConservationContactHeaderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Eccube\Entity\Customer;

/**
 * @ORM\Entity(repositoryClass=ConservationContactHeaderRepository::class)
 * @ORM\Table(name="ald_conservation_contact_header")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discriminator_type", type="string", length=255)
 * @ORM\HasLifecycleCallbacks()
 */
class ConservationContactHeader
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(name="id", type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Customer::class, inversedBy="ConservationContactHeader")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="customer_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $Customer;

    /**
     * @ORM\ManyToOne(targetEntity=Conservations::class, inversedBy="ConservationContactHeader")
     * @ORM\JoinColumn(name="conservation_id", nullable=false)
     */
    private $Conservation;

    /**
     * @ORM\ManyToOne(targetEntity=ConservationPets::class)
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="pet_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $Pet;

    /**
     * @ORM\Column(name="contact_type", type="smallint", nullable=false)
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
     * @ORM\Column(name="send_date", type="datetime", nullable=false)
     */
    private $send_date;

    /**
     * @ORM\Column(name="last_message_date", type="datetime", nullable=true)
     */
    private $last_message_date;

    /**
     * @ORM\Column(name="contract_status", type="smallint", options={"default" = 0}, nullable=false)
     */
    private $contract_status = 0;

    /**
     * @ORM\Column(name="sendoff_reason", type="smallint", options={"default" = 0}, nullable=false)
     */
    private $sendoff_reason = 0;

    /**
     * @ORM\Column(name="customer_check", type="smallint", options={"default" = 0}, nullable=false)
     */
    private $customer_check = 0;

    /**
     * @ORM\Column(name="conservation_check", type="smallint", options={"default" = 0}, nullable=false)
     */
    private $conservation_check = 0;

    /**
     * @ORM\Column(name="conservation_new_msg", type="smallint", options={"default" = 1}, nullable=false)
     */
    private $conservation_new_msg = 1;

    /**
     * @ORM\Column(name="customer_new_msg", type="smallint", options={"default" = 0}, nullable=false)
     */
    private $customer_new_msg = 0;

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
     * @ORM\OneToMany(targetEntity=ConservationContacts::class, mappedBy="ConservationContactHeader")
     */
    private $ConservationContacts;

    /**
     * @ORM\Column(name="image_file", type="string", length=255, nullable=true)
     */
    private $image_file;
    
    public function __construct()
    {
        $this->ConservationContacts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCustomer(): ?Customer
    {
        return $this->Customer;
    }

    public function setCustomer(?Customer $customer): self
    {
        $this->Customer = $customer;

        return $this;
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

    public function getSendDate(): ?\DateTimeInterface
    {
        return $this->send_date;
    }

    public function setSendDate(\DateTimeInterface $send_date): self
    {
        $this->send_date = $send_date;

        return $this;
    }

    public function getLastMessageDate(): ?\DateTimeInterface
    {
        return $this->last_message_date;
    }

    public function setLastMessageDate(?\DateTimeInterface $last_message_date): self
    {
        $this->last_message_date = $last_message_date;

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

    public function getSendoffReason(): ?int
    {
        return $this->sendoff_reason;
    }

    public function setSendoffReason(int $sendoff_reason): self
    {
        $this->sendoff_reason = $sendoff_reason;

        return $this;
    }

    public function getCustomerCheck(): ?int
    {
        return $this->customer_check;
    }

    public function setCustomerCheck(int $customer_check): self
    {
        $this->customer_check = $customer_check;

        return $this;
    }

    public function getConservationCheck(): ?int
    {
        return $this->conservation_check;
    }

    public function setConservationCheck(int $conservation_check): self
    {
        $this->conservation_check = $conservation_check;

        return $this;
    }

    public function getConservationNewMsg(): ?int
    {
        return $this->conservation_new_msg;
    }

    public function setConservationNewMsg(int $conservation_new_msg): self
    {
        $this->conservation_new_msg = $conservation_new_msg;

        return $this;
    }

    public function getCustomerNewMsg(): ?int
    {
        return $this->customer_new_msg;
    }

    public function setCustomerNewMsg(int $customer_new_msg): self
    {
        $this->customer_new_msg = $customer_new_msg;

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
     * @return Collection|ConservationContacts[]
     */
    public function getConservationContacts(): Collection
    {
        return $this->ConservationContacts;
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
