<?php

namespace Customize\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Annotation\EntityExtension;

/**
 * @EntityExtension("Eccube\Entity\Customer")
 */
trait CustomerTrait
{
    /**
     * @ORM\Column(name="is_breeder",type="smallint", options={"default" : 0})
     */
    public $is_breeder;

    /**
     * @ORM\Column(name="is_conservation",type="smallint", options={"default" : 0})
     */
    public $is_conservation;

    /**
     * @ORM\Column(name="regist_type",type="smallint", options={"default" : 0})
     */
    public $regist_type;

    /**
     * @ORM\Column(name="site_type", type="smallint", nullable=true)
     */
    public $site_type;

    /**
     * @ORM\Column(name="register_id", type="integer", nullable=true)
     */
    public $register_id;

    /**
     * @ORM\Column(name="relation_id", type="integer", nullable=true, options={"default" : 0})
     */
    public $relation_id;

    public function setRelationId($relation_id)
    {
        $this->relation_id = $relation_id;

        return $this;
    }
    public function getRelationId()
    {
        return $this->relation_id;
    }

    /**
     * Set regist_type.
     *
     * @param string $regist_type
     *
     * @return Customer
     */
    public function setRegistType($regist_type)
    {
        $this->regist_type = $regist_type;

        return $this;
    }

    /**
     * Get regist type.
     *
     * @return integer
     */
    public function getRegistType()
    {
        return $this->regist_type;
    }

    /**
     * Get site_type.
     *
     * @return ?int
     */
    public function getSiteType(): ?int
    {
        return $this->site_type;
    }

    /**
     * Set site_type.
     *
     * @param ?int $site_type
     *
     * @return Customer
     */
    public function setSiteType(?int $site_type): self
    {
        $this->site_type = $site_type;

        return $this;
    }

    /**
     * Get register_id.
     *
     * @return ?int
     */
    public function getRegisterId(): ?int
    {
        return $this->register_id;
    }

    /**
     * Set register_id.
     *
     * @param ?int $register_id
     *
     * @return Customer
     */
    public function setRegisterId(?int $register_id): self
    {
        $this->register_id = $register_id;

        return $this;
    }

    /**
     * Set is_breeder.
     *
     * @param string $is_breeder
     *
     * @return Customer
     */
    public function setIsBreeder($is_breeder)
    {
        $this->is_breeder = $is_breeder;

        return $this;
    }

    /**
     * Get is_breeder.
     *
     * @return integer
     */
    public function getIsBreeder()
    {
        return $this->is_breeder;
    }

    /**
     * Set is_conservation.
     *
     * @param string $is_conservation
     *
     * @return Customer
     */
    public function setIsConservation($is_conservation)
    {
        $this->is_conservation = $is_conservation;

        return $this;
    }

    /**
     * Get is_conservation.
     *
     * @return integer
     */
    public function getIsConservation()
    {
        return $this->is_conservation;
    }

    //このメソッドはProxyに反映されません。再生成時には手動でProxyにコピーすること
    /**
     * {@inheritdoc}
     */
    public function getRoles()
    {
        $roles = ['ROLE_USER'];
        if ($this->is_breeder) {
            array_push($roles, "ROLE_BREEDER_USER");
        }
        if ($this->is_conservation) {
            array_push($roles, "ROLE_CONSERVATION_USER");
        }

        return $roles;
    }
}
