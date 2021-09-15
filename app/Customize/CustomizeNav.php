<?php

namespace Customize;

use Eccube\Common\EccubeNav;

class CustomizeNav implements EccubeNav
{
	/**
	 * @return array
	 */
	public static function getNav()
	{
		return [
			// 第一階層からオリジナルのメニューを追加する場合のサンプル
			'adoptions' => [
				'name' => '保護団体管理',
				'icon' => 'fa-cube',
				'children' => [
					'adoptions_adoption_list' => [
						'name' => '保護団体一覧',
						'url' => 'admin_adoption_list',
					],
				],
			],
			'breeders' => [
				'name' => 'ブリーダー管理',
				'icon' => 'fa-cube',
				'children' => [
					'breeders_breeder_list' => [
						'name' => 'ブリーダー一覧',
						'url' => 'admin_breeder_list',
					],
				],
			],
			'product' => [
				'children' => [
					'supplier' => [
						'name' => '仕入先管理',
						'url' => 'admin_product_supplier',
					],
					'waste' => [
						'name' => '廃棄管理',
						'url' => 'admin_product_waste',
					],
					'instock' => [
						'name' => '入荷情報登録',
						'url' => 'admin_product_instock',
					],
				],
			],
			'order' => [
				'children' => [
					'shipping_instructions' => [
						'name' => '出荷指示管理',
						'url' => 'admin_shipping_instructions',
					],
				],
			],
		];
	}
}
