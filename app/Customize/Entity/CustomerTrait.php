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
     * @ORM\Column(name="is_breeder", type="string", nullable=true)
     */
    private $is_breeder;

    /**
     * @ORM\Column(name="is_conservation", type="string", nullable=true)
     */
    private $is_conservation;

    /**
     * @ORM\Column(name="regist_type", type="string", nullable=true)
     */
    private $regist_type;

    /**
     * @ORM\Column(name="site_type", type="smallint", nullable=true)
     */
    public $site_type;

    /**
     * @ORM\Column(name="register_id", type="integer", nullable=true)
     */
    public $register_id;

    /**
     * @ORM\Column(name="relation_id", type="string", nullable=true)
     */
    private $relation_id;

    public function setRelationId(?string $relationId): self
    {
        $this->relation_id = $relationId;

        return $this;
    }
    
    public function getRelationId(): ?string
    {
        return $this->relation_id;
    }

    /**
     * Set regist_type.
     *
     * @param string|null $registType
     *
     * @return Customer
     */
    public function setRegistType(?string $registType): self
    {
        $this->regist_type = $registType;

        return $this;
    }

    /**
     * Get regist type.
     *
     * @return string|null
     */
    public function getRegistType(): ?string
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
     * @param int $isBreeder
     *
     * @return Customer
     */
    public function setIsBreeder(int $isBreeder): self
    {
        $this->is_breeder = $isBreeder;

        return $this;
    }

    /**
     * Get is_breeder.
     *
     * @return string|null
     */
    public function getIsBreeder(): ?string
    {
        return $this->is_breeder;
    }

    /**
     * Set is_conservation.
     *
     * @param int $isConservation
     *
     * @return Customer
     */
    public function setIsConservation(int $isConservation): self
    {
        $this->is_conservation = $isConservation;

        return $this;
    }

    /**
     * Get is_conservation.
     *
     * @return string|null
     */
    public function getIsConservation(): ?string
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
