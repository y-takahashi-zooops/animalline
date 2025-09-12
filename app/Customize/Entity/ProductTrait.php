<?php

namespace Customize\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Annotation\EntityExtension;

/**
 * @EntityExtension("Eccube\Entity\Product")
 */
trait ProductTrait
{
    /**
     * @ORM\Column(name="item_weight", type="decimal", precision=6, scale=2, nullable=true)
     */
    private $item_weight;

    /**
     * @ORM\Column(name="maker_id", type="integer", nullable=true)
     */
    public $maker_id;

    /**
     * @ORM\Column(name="is_check_auth", type="boolean", nullable=true)
     */
    private $is_check_auth;

    /**
     * Set item_weight.
     *
     * @param float|null $item_weight
     *
     * @return Product
     */
    public function setItemWeight(?float $item_weight): self
    {
        $this->item_weight = $item_weight;

        return $this;
    }

    /**
     * Get item_weight.
     *
     * @return float|null
     */
    public function getItemWeight(): ?float
    {
        return $this->item_weight;
    }

    /**
     * Set maker_id.
     *
     * @param integer $maker_id
     *
     * @return Product
     */
    public function setMakerId($maker_id)
    {
        $this->maker_id = $maker_id;

        return $this;
    }

    /**
     * Get maker_id.
     *
     * @return integer
     */
    public function gettMakerId()
    {
        return $this->maker_id;
    }

    /**
     * Set is_check_auth.
     *
     * @param ?bool $is_check_auth
     *
     * @return Product
     */
    public function setIsCheckAuth(?bool $is_check_auth): self
    {
        $this->is_check_auth = $is_check_auth;

        return $this;
    }

    /**
     * Get is_check_auth.
     *
     * @return bool
     */
    public function getIsCheckAuth(): ?bool
    {
        return $this->is_check_auth;
    }
}
