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
    * @ORM\Column(name="quantity_box",type="integer", nullable=false, options={"default":0})
    */
    public $quantity_box;

    /**
    * @ORM\Column(name="item_weight", type="decimal", precision=5, scale=2, nullable=false, options={"default":0})
    */
    public $item_weight;

    /**
    * Set quantity_box.
    *
    * @param integer $quantity_box
    *
    * @return Product
    */
    public function setQuantityBox($quantity_box)
    {
        $this->quantity_box = $quantity_box;

        return $this;
    }

    /**
    * Get quantity_box.
    *
    * @return integer
    */
    public function getQuantityBox()
    {
        return $this->quantity_box;
    }

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
}
