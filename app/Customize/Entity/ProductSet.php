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
    private $parentProduct;

    /**
     * @ORM\ManyToOne(targetEntity=Product::class, inversedBy="ProductSet")
     * @ORM\JoinColumn(name="product_id", nullable=true)
     */
    private $Product;

    /**
     * @ORM\ManyToOne(targetEntity=ProductClass::class, inversedBy="ProductSet")
     * @ORM\JoinColumn(name="product_class_id", nullable=false)
     */
    private $ProductClass;

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
        return $this->parentProduct;
    }

    public function setParentProduct(Product $parentProduct): self
    {
        $this->parentProduct = $parentProduct;

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
