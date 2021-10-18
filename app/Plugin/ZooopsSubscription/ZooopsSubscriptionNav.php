<?php

namespace Plugin\ZooopsSubscription;

use Eccube\Common\EccubeNav;

class ZooopsSubscriptionNav implements EccubeNav
{
    /**
     * @return array
     */
    public static function getNav()
    {
        $menu = [
            'order' => [
                'children' => [
                    'admin_zooops_subscription' => [
                        'name' => 'admin.order.subscription',
                        'url' => 'admin_zooops_subscription_view',
                    ],
                ],
            ],
        ];

        return $menu;
    }
}
