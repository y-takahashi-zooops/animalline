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
    * @ORM\Column(name="jan_code",type="string", length=13, nullable=true)
    */
    public $jan_code;

    /**
    * @ORM\Column(name="stock_code",type="string", length=5, nullable=false)
    */
    public $stock_code;

    /**
    * @ORM\Column(name="item_cost", type="decimal", precision=12, scale=2, nullable=false, options={"default":0})
    */
    public $item_cost;

    /**
     * @ORM\Column(name="incentive_ratio", type="integer", nullable=false, options={"default":5})
     */
    private $incentive_ratio;

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
    * Get item_cost.
    *
    * @return integer
    */
    public function getItemCost()
    {
        return $this->item_cost;
    }

    /**
    * Set jan_code.
    *
    * @param string $jan_code
    *
    * @return ProductClass
    */
    public function setJanCode($jan_code)
    {
        $this->jan_code = $jan_code;

        return $this;
    }

    /**
    * Get jan_code.
    *
    * @return string
    */
    public function getJanCode()
    {
        return $this->jan_code;
    }

    /**
    * Set stock_code.
    *
    * @param string $stock_code
    *
    * @return ProductClass
    */
    public function setStockCode($stock_code)
    {
        $this->stock_code = $stock_code;

        return $this;
    }

    /**
    * Get stock_code.
    *
    * @return string
    */
    public function getStockCode()
    {
        return $this->stock_code;
    }

    /**
    * Set stock_code.
    *
    * @param string $incentive_ratio
    *
    * @return ProductClass
    */
    public function setIncentiveRatio($incentive_ratio)
    {
        $this->incentive_ratio = $incentive_ratio;

        return $this;
    }

    /**
    * Get incentive_ratio.
    *
    * @return string
    */
    public function getIncentiveRatio()
    {
        return $this->incentive_ratio;
    }
}
