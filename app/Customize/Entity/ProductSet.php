<?php

namespace Customize\Entity;

use Customize\Repository\ProductSetRepository;
use Doctrine\ORM\Mapping as ORM;
use Eccube\Entity\Product;
use Eccube\Entity\ProductClass;

/**
 * @ORM\Table(name="ald_product_set")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discriminator_type", type="string", length=255)
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Entity(repositoryClass=ProductSetRepository::class)
 */
class ProductSet
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(name="id", type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Product::class, inversedBy="ProductSet")
     * @ORM\JoinColumn(name="parent_product_id", nullable=true)
     */
    private $ParentProduct;

    /**
     * @ORM\ManyToOne(targetEntity=ProductClass::class)
     * @ORM\JoinColumn(name="parent_product_class_id", nullable=true)
     */
    private $ParentProductClass;

    /**
     * @ORM\ManyToOne(targetEntity=Product::class, inversedBy="ProductSet")
     * @ORM\JoinColumn(name="product_id", nullable=true)
     */
    private $Product;

    /**
     * @ORM\ManyToOne(targetEntity=ProductClass::class, inversedBy="ProductSet")
     * @ORM\JoinColumn(name="product_class_id", nullable=true)
     */
    private $ProductClass;

    /**
     * @ORM\Column(name="set_unit", type="smallint", nullable=true)
     */
    private $set_unit;

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

    public function getParentProduct(): ?Product
    {
        return $this->ParentProduct;
    }

    public function setParentProduct(Product $ParentProduct): self
    {
        $this->ParentProduct = $ParentProduct;

        return $this;
    }

    public function getParentProductClass(): ?ProductClass
    {
        return $this->ParentProductClass;
    }

    public function setParentProductClass(ProductClass $ParentProductClass): self
    {
        $this->ParentProductClass = $ParentProductClass;

        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->Product;
    }

    public function setProduct(Product $Product): self
    {
        $this->Product = $Product;

        return $this;
    }

    public function getProductClassId(): ?ProductClass
    {
        return $this->ProductClass;
    }

    public function setProductClassId(ProductClass $ProductClass): self
    {
        $this->ProductClass = $ProductClass;

        return $this;
    }

    public function getSetUnit(): ?int
    {
        return $this->set_unit;
    }

    public function setSetUnit(int $set_unit): self
    {
        $this->set_unit = $set_unit;

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
