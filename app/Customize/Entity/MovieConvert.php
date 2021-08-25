<?php

namespace Customize\Entity;

use Customize\Repository\MovieConvertRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MovieConvertRepository::class)
 * @ORM\Table(name="ald_movie_convert")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discriminator_type", type="string", length=255)
 * @ORM\HasLifecycleCallbacks()
 */
class MovieConvert
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(name="id", type="integer")
     */
    private $id;

    /**
     * @ORM\Column(name="source_path", type="string", length=255)
     */
    private $source_path;

    /**
     * @ORM\Column(name="dist_path", type="string", length=255)
     */
    private $dist_path;

    /**
     * @ORM\Column(name="site_category", type="smallint")
     */
    private $site_category;

    /**
     * @ORM\Column(name="pet_id", type="integer")
     */
    private $pet_id;

    /**
     * @ORM\Column(name="convert_status", type="smallint", options={"default" = 0})
     */
    private $convert_status;

    /**
     * @ORM\Column(name="error_reason", type="text", nullable=true)
     */
    private $error_reason;

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

    public function getSourcePath(): ?string
    {
        return $this->source_path;
    }

    public function setSourcePath(string $source_path): self
    {
        $this->source_path = $source_path;

        return $this;
    }

    public function getDistPath(): ?string
    {
        return $this->dist_path;
    }

    public function setDistPath(string $dist_path): self
    {
        $this->dist_path = $dist_path;

        return $this;
    }

    public function getSiteCategory(): ?int
    {
        return $this->site_category;
    }

    public function setSiteCategory(int $site_category): self
    {
        $this->site_category = $site_category;

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

    public function getConvertStatus(): ?int
    {
        return $this->convert_status;
    }

    public function setConvertStatus(int $convert_status): self
    {
        $this->convert_status = $convert_status;

        return $this;
    }

    public function getErrorReason(): ?string
    {
        return $this->error_reason;
    }

    public function setErrorReason(?string $error_reason): self
    {
        $this->error_reason = $error_reason;

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
