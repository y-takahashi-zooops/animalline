<?php

namespace Customize\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Annotation\EntityExtension;

/**
 * @EntityExtension("Eccube\Entity\Order")
 */
trait OrderTrait
{

  /**
   * @var Plugin\ZooopsSubscription\Entity\SubscriptionContract
   *
   * @ORM\ManyToOne(targetEntity="Plugin\ZooopsSubscription\Entity\SubscriptionContract")
   * @ORM\JoinColumns({
   *   @ORM\JoinColumn(name="subscription_id", referencedColumnName="id")
   * })
   */
  private $SubscriptionContract;


  /**
   * Set SubscriptionContract.
   *
   * @param \Plugin\ZooopsSubscription\Entity\SubscriptionContract|null $subscriptionContract
   *
   * @return Order
   */
  public function setSubscriptionContract(\Plugin\ZooopsSubscription\Entity\SubscriptionContract $subscriptionContract = null)
  {
    $this->SubscriptionContract = $subscriptionContract;

    return $this;
  }

  /**
   * Get SubscriptionContract.
   *
   * @return \Plugin\ZooopsSubscription\Entity\SubscriptionContract|null
   */
  public function getSubscriptionContract()
  {
    return $this->SubscriptionContract;
  }
}
