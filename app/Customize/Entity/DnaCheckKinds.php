<?php

namespace Customize\Entity;

use Customize\Repository\DnaCheckKindsRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="alm_dna_check_kinds")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discriminator_type", type="string", length=255)
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Entity(repositoryClass=DnaCheckKindsRepository::class)
 */
class DnaCheckKinds
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id", type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Breeds::class)
     * @ORM\JoinColumn(name="breeds_id", nullable=false)
     */
    private $Breeds;

    /**
     * @ORM\Column(name="check_kind" , type="string", length=64, nullable=false)
     */
    private $check_kind;

    /**
     * @ORM\Column(name="delete_flg", type="smallint", nullable=false, options={"default" = 0})
     */
    private $delete_flg = 0;

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

    public function getBreeds(): ?Breeds
    {
        return $this->Breeds;
    }

    public function setBreeds(?Breeds $Breeds): self
    {
        $this->Breeds = $Breeds;

        return $this;
    }

    public function getCheckKind(): ?string
    {
        return $this->check_kind;
    }

    public function setCheckKind(string $check_kind): self
    {
        $this->check_kind = $check_kind;

        return $this;
    }

    public function getDeleteFlg(): ?int
    {
        return $this->delete_flg;
    }

    public function setDeleteFlg(int $delete_flg): self
    {
        $this->delete_flg = $delete_flg;

        return $this;
    }

    /**
     * Set createDate.
     *
     * @param \DateTime $createDate
     *
     * @return DnaCheckKinds
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
     * @return DnaCheckKinds
     */
    public function setUpdateDate($updateDate)
    {
        $this->update_date = $updateDate;

        return $this;
    }
}
