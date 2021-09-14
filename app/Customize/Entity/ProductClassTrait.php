<?php

namespace Customize\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Annotation\EntityExtension;

/**
 * @EntityExtension("Eccube\Entity\ProductClass")
 */
trait ProductClassTrait
{
    /**
    * @ORM\Column(name="supplier_code",type="string", length=5, nullable=true)
    */
    public $supplier_code;

    /**
    * @ORM\Column(name="item_cost", type="decimal", precision=12, scale=2, nullable=false, options={"default":0})
    */
    public $item_cost;

    /**
    * Set supplier_code.
    *
    * @param string $supplier_code
    *
    * @return ProductClass
    */
    public function setSupplierCode($supplier_code)
    {
        $this->supplier_code = $supplier_code;

        return $this;
    }

    /**
    * Get supplier_code.
    *
    * @return integer
    */
    public function getSupplierCode()
    {
        return $this->supplier_code;
    }

    /**
    * Set item_cost.
    *
    * @param string $item_cost
    *
    * @return ProductClass
    */
    public function setItemCost($item_cost)
    {
        $this->item_cost = $item_cost;

        return $this;
    }

    /**
    * Get is_breeder.
    *
    * @return integer
    */
    public function getItemCost()
    {
        return $this->item_cost;
    }
}
