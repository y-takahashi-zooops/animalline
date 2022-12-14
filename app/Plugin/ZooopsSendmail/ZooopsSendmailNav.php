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
                        'name' => 'テンプレート管理',
                        'url' => 'admin_zooops_sendmail_template',
                    ],
                    'admin_zooops_sendmail_send' => [
                            'name' => '一斉メール送信',
                            'url' => 'admin_zooops_sendmail_send',
                        ],
                ],
            ],
        ];

        return $menu;
    }
}
