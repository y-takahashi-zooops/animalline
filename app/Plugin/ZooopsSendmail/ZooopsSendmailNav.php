<?php

namespace Plugin\ZooopsSendmail;

use Eccube\Common\EccubeNav;

class ZooopsSendmailNav implements EccubeNav
{
    /**
     * @return array
     */
    public static function getNav()
    {
        $menu = [
            'customer' => [
                'children' => [
                    'admin_zooops_sendmail_template' => [
                        'name' => 'admin.customer.customer_template',
                        'url' => 'admin_zooops_sendmail_template',
                    ],
                    'admin_zooops_sendmail_send' => [
                            'name' => 'admin.customer.customer_all_mail_send',
                            'url' => 'admin_zooops_sendmail_send',
                        ],
                ],
            ],
        ];

        return $menu;
    }
}
