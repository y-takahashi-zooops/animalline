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
		if($this->is_breeder){
			array_push($roles,"ROLE_BREEDER_USER");
		}
		if($this->is_conservation){
			array_push($roles,"ROLE_CONSERVATION_USER");
		}

		return $roles;
	}

}
