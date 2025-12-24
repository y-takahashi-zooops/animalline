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
    * @ORM\Column(name="supplier_code", type="string", nullable=true)
    */
    private $supplier_code;

    /**
    * @ORM\Column(name="jan_code", type="string", length=13, nullable=true)
    */
    private $jan_code;

    /**
    * @ORM\Column(name="stock_code", type="string", length=5, nullable=true)
    */
    private $stock_code;

    /**
    * @ORM\Column(name="item_cost", type="decimal", precision=12, scale=2, nullable=true)
    */
    private $item_cost;

    /**
     * @ORM\Column(name="incentive_ratio", type="decimal", precision=5, scale=2, nullable=true)
     */
    private $incentive_ratio;

    /**
    * Set supplier_code.
    *
    * @param string|null $supplier_code
    *
    * @return ProductClass
    */
    public function setSupplierCode(?string $supplier_code): self
    {
        $this->supplier_code = $supplier_code;

        return $this;
    }

    /**
    * Get supplier_code.
    *
    * @return string|null
    */
    public function getSupplierCode(): ?string
    {
        return $this->supplier_code;
    }

    /**
    * Set item_cost.
    *
    * @param int|null $itemCost
    *
    * @return ProductClass
    */
    public function setItemCost(?int $itemCost): self
    {
        $this->item_cost = $itemCost;

        return $this;
    }

    /**
    * Get item_cost.
    *
    * @return float|null
    */
    public function getItemCost(): ?float
    {
        return $this->item_cost;
    }

    /**
    * Set jan_code.
    *
    * @param string|null $jan_code
    *
    * @return ProductClass
    */
    public function setJanCode(?string $jan_code): self
    {
        $this->jan_code = $jan_code;

        return $this;
    }

    /**
    * Get jan_code.
    *
    * @return string|null
    */
    public function getJanCode(): ?string
    {
        return $this->jan_code;
    }

    /**
    * Set stock_code.
    *
    * @param string|null $stock_code
    *
    * @return ProductClass
    */
    public function setStockCode(?string $stock_code): self
    {
        $this->stock_code = $stock_code;

        return $this;
    }

    /**
    * Get stock_code.
    *
    * @return string|null
    */
    public function getStockCode(): ?string
    {
        return $this->stock_code;
    }

    /**
    * Set incentive_ratio.
    *
    * @param float|null $incentive_ratio
    *
    * @return ProductClass
    */
    public function setIncentiveRatio(?float $incentive_ratio): self
    {
        $this->incentive_ratio = $incentive_ratio;

        return $this;
    }

    /**
    * Get incentive_ratio.
    *
    * @return float|null
    */
    public function getIncentiveRatio(): ?float
    {
        return $this->incentive_ratio;
    }
}
