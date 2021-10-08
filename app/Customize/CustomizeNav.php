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
			'dna' => [
				'name' => 'DNA検査',
				'icon' => 'fa-vial',
				'children' => [
					'dna_examination_status' => [
						'name' => '検査状況確認',
						'url' => 'admin_dna_examination_status',
					],
				],
			],
			'product' => [
				'children' => [
					'supplier' => [
						'name' => '仕入先管理',
						'url' => 'admin_product_supplier',
					],
					'maker' => [
						'name' => 'プロデューサー管理',
						'url' => 'admin_product_maker',
					],
					'waste' => [
						'name' => '廃棄管理',
						'url' => 'admin_product_waste',
					],
					'instock_master' => [
						'name' => '入荷情報一覧',
						'url' => 'admin_product_instock_list',
					],
					'instock_edit' => [
						'name' => '入荷情報登録',
						'url' => 'admin_product_instock_registration_new',
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
