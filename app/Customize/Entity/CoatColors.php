<?php

namespace Customize\Entity;

use Customize\Repository\CoatColorsRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="alm_coat_colors")
 * @ORM\Entity(repositoryClass=CoatColorsRepository::class)
 */
class CoatColors
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="smallint")
     */
    private $pet_kind;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $coat_color_name;

    /**
     * @ORM\Column(type="integer")
     */
    private $sort_oder;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPetKind(): ?int
    {
        return $this->pet_kind;
    }

    public function setPetKind(int $pet_kind): self
    {
        $this->pet_kind = $pet_kind;

        return $this;
    }

    public function getCoatColorName(): ?string
    {
        return $this->coat_color_name;
    }

    public function setCoatColorName(string $coat_color_name): self
    {
        $this->coat_color_name = $coat_color_name;

        return $this;
    }

    public function getSortOder(): ?int
    {
        return $this->sort_oder;
    }

    public function setSortOder(int $sort_oder): self
    {
        $this->sort_oder = $sort_oder;

        return $this;
    }
}
