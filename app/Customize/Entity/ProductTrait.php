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
    * @ORM\Column(name="item_weight", type="decimal", precision=5, scale=2, nullable=false, options={"default":0})
    */
    public $item_weight;

    /**
    * @ORM\Column(name="maker_id", type="integer", nullable=false)
    */
    public $maker_id;

    /**
    * Set item_weight.
    *
    * @param string $item_weight
    *
    * @return Product
    */
    public function setItemWeight($item_weight)
    {
        $this->item_weight = $item_weight;

        return $this;
    }

    /**
    * Get item_weight.
    *
    * @return integer
    */
    public function getItemWeight()
    {
        return $this->item_weight;
    }

    /**
    * Set maker_id.
    *
    * @param string $maker_id
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
}
