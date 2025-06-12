<?php

namespace Customize\Twig;

use Eccube\Entity\Product;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ProductExtension extends AbstractExtension
{
    // public function getFunctions(): array
    // {
    //     return [
    //         new TwigFunction('class_categories_as_json', [$this, 'getClassCategoriesAsJson']),
    //     ];
    // }

    // public function getClassCategoriesAsJson(Product $Product): string
    // {
    //     $class_categories = [
    //         '__unselected' => [
    //             '__unselected' => [
    //                 'name' => 'trans:common.select',
    //                 'product_class_id' => '',
    //             ],
    //         ],
    //     ];

    //     foreach ($Product->getProductClasses() as $ProductClass) {
    //         if (!$ProductClass->isVisible()) {
    //             continue;
    //         }

    //         $ClassCategory1 = $ProductClass->getClassCategory1();
    //         $ClassCategory2 = $ProductClass->getClassCategory2();
    //         if ($ClassCategory2 && !$ClassCategory2->isVisible()) {
    //             continue;
    //         }

    //         $class_category_id1 = $ClassCategory1 ? (string) $ClassCategory1->getId() : '__unselected2';
    //         $class_category_id2 = $ClassCategory2 ? (string) $ClassCategory2->getId() : '';

    //         $class_category_name2 = $ClassCategory2
    //             ? $ClassCategory2->getName() . (!$ProductClass->getStockFind() ? ' trans:front.product.out_of_stock_label' : '')
    //             : 'trans:common.select';

    //         // デフォルト項目
    //         if (!isset($class_categories[$class_category_id1]['#'])) {
    //             $class_categories[$class_category_id1]['#'] = [
    //                 'classcategory_id2' => '',
    //                 'name' => 'trans:common.select',
    //                 'product_class_id' => '',
    //             ];
    //         }

    //         $class_categories[$class_category_id1]['#' . $class_category_id2] = [
    //             'classcategory_id2' => $class_category_id2,
    //             'name' => $class_category_name2,
    //             'stock_find' => $ProductClass->getStockFind(),
    //             'price01' => $ProductClass->getPrice01() === null ? '' : number_format($ProductClass->getPrice01()),
    //             'price02' => number_format($ProductClass->getPrice02()),
    //             'price01_inc_tax' => $ProductClass->getPrice01() === null ? '' : number_format($ProductClass->getPrice01IncTax()),
    //             'price02_inc_tax' => number_format($ProductClass->getPrice02IncTax()),
    //             'product_class_id' => (string) $ProductClass->getId(),
    //             'product_code' => $ProductClass->getCode() ?? '',
    //             'sale_type' => $ProductClass->getSaleType() ? (string) $ProductClass->getSaleType()->getId() : '',
    //             'item_cost' => method_exists($ProductClass, 'getItemCost') ? (float) $ProductClass->getItemCost() : 0.0,
    //         ];
    //     }

    //     return json_encode($class_categories, JSON_UNESCAPED_UNICODE);
    // }
}
