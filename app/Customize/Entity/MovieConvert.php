<?php

namespace Customize\Entity;

use Customize\Repository\MovieConvertRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PetsFavoriteRepository::class)
 * @ORM\Table(name="ald_movie_convert")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discriminator_type", type="string", length=255)
 * @ORM\HasLifecycleCallbacks()
 */
class MovieConvert
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $source_path;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $dist_path;

    /**
     * @ORM\Column(type="smallint")
     */
    private $site_category;

    /**
     * @ORM\Column(type="integer")
     */
    private $pet_id;

    /**
     * @ORM\Column(type="smallint", options={"default" = 0})
     */
    private $convert_status;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $error_reason;

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
}
