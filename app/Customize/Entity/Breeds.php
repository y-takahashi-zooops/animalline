<?php

namespace Customize\Entity;

use Customize\Repository\BreedsRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=BreedsRepository::class)
 */
class Breeds
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
    private $breeds_name;

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

    public function getBreedsName(): ?string
    {
        return $this->breeds_name;
    }

    public function setBreedsName(string $breeds_name): self
    {
        $this->breeds_name = $breeds_name;

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
