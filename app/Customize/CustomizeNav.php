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
					'breeders_dna_check_list' => [
						'name' => '検査状況集計',
						'url' => 'admin_breeder_dna_check_list',
					],
					'breeders_evaluation' => [
						'name' => '評価一覧',
						'url' => 'admin_breeder_evaluation',
					],
				],
			],
			'pets' => [
				'name' => 'ペット管理',
				'icon' => 'fa-cube',
				'children' => [
					'pet_list' => [
						'name' => 'ペット情報管理',
						'url' => 'admin_pet_all',
					],
				],
			],
			'dna' => [
				'name' => 'DNA検査',
				'icon' => 'fa-vial',
				'children' => [
					'dna_examination_items' => [
						'name' => '検査項目管理',
						'url' => 'admin_dna_examination_items',
					],
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
						'name' => 'メーカー管理',
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
			'benefits' => [
				'name' => '特典管理',
				'icon' => 'fa-gift',
				'children' => [
					'benefits_delivery_status' => [
						'name' => '発送状況管理',
						'url' => 'admin_benefits_delivery_status',
					],
				],
			],
		];
	}
}
