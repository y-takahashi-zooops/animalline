<?php

/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) EC-CUBE CO.,LTD. All Rights Reserved.
 *
 * http://www.ec-cube.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eccube\Controller\Admin\Product;

use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Eccube\Common\Constant;
use Eccube\Controller\Admin\AbstractCsvImportController;
use Eccube\Entity\Category;
use Eccube\Entity\ClassCategory;
use Eccube\Entity\ClassName;
use Eccube\Entity\Product;
use Eccube\Entity\ProductCategory;
use Eccube\Entity\ProductClass;
use Eccube\Entity\ProductImage;
use Eccube\Entity\ProductStock;
use Eccube\Entity\ProductTag;
use Eccube\Form\Type\Admin\CsvImportType;
use Eccube\Repository\CategoryRepository;
use Eccube\Repository\ClassNameRepository;
use Eccube\Repository\ClassCategoryRepository;
use Eccube\Repository\DeliveryDurationRepository;
use Eccube\Repository\Master\ProductStatusRepository;
use Eccube\Repository\ProductClassRepository;
use Eccube\Repository\ProductRepository;
use Eccube\Repository\TagRepository;
use Eccube\Service\CsvImportService;
use Eccube\Stream\Filter\ConvertLineFeedFilter;
use Eccube\Stream\Filter\SjisToUtf8EncodingFilter;
use Eccube\Util\CacheUtil;
use Eccube\Util\StringUtil;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Psr\Log\LoggerInterface;
use Eccube\Common\EccubeConfig;
use Doctrine\ORM\EntityManagerInterface;

class CsvImportController extends AbstractCsvImportController
{
    /**
     * @var DeliveryDurationRepository
     */
    protected $deliveryDurationRepository;

    /**
     * @var TagRepository
     */
    protected $tagRepository;

    /**
     * @var CategoryRepository
     */
    protected $categoryRepository;

    /**
     * @var ClassCategoryRepository
     */
    protected $classCategoryRepository;

    /**
     * @var ProductStatusRepository
     */
    protected $productStatusRepository;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var ProductClassRepository
     */
    protected $productClassRepository;

    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * @var LoggerInterface
     */
    protected $logger;
    
    /**
     * @var ClassNameRepository
     */
    protected $classNameRepository;

    protected FormFactoryInterface $formFactory;

    private $errors = [];
    private \HTMLPurifier $purifier;

    /**
     * CsvImportController constructor.
     *
     * @param DeliveryDurationRepository $deliveryDurationRepository
     * @param TagRepository $tagRepository
     * @param CategoryRepository $categoryRepository
     * @param ClassNameRepository $classNameRepository
     * @param ClassCategoryRepository $classCategoryRepository
     * @param ProductStatusRepository $productStatusRepository
     * @param ProductRepository $productRepository
     * @param ProductClassRepository $ProductClassRepository
     * @param ValidatorInterface $validator
     * @param LoggerInterface $logger
     * @param \HTMLPurifier $purifier
     * 
     * @throws \Exception
     */
    public function __construct(
        DeliveryDurationRepository $deliveryDurationRepository,
        TagRepository $tagRepository,
        CategoryRepository $categoryRepository,
        ClassNameRepository $classNameRepository,
        ClassCategoryRepository $classCategoryRepository,
        ProductStatusRepository $productStatusRepository,
        ProductRepository $productRepository,
        ProductClassRepository $productClassRepository,
        ValidatorInterface $validator,
        FormFactoryInterface $formFactory,
        LoggerInterface $logger,
        EccubeConfig $eccubeConfig,
        EntityManagerInterface $entityManager,
        \HTMLPurifier $purifier,
    ) {
        $this->deliveryDurationRepository = $deliveryDurationRepository;
        $this->tagRepository = $tagRepository;
        $this->categoryRepository = $categoryRepository;
        $this->classNameRepository = $classNameRepository;
        $this->classCategoryRepository = $classCategoryRepository;
        $this->productStatusRepository = $productStatusRepository;
        $this->productRepository = $productRepository;
        $this->productClassRepository = $productClassRepository;
        $this->validator = $validator;
        $this->formFactory = $formFactory;
        $this->logger = $logger;
        $this->eccubeConfig = $eccubeConfig;
        $this->entityManager = $entityManager;
        $this->purifier = $purifier;
    }

    /**
     * 商品登録CSVアップロード
     *
     * @Route("/%eccube_admin_route%/product/product_csv_upload", name="admin_product_csv_import", methods={"GET", "POST"})
     *
     * @Template("@admin/Product/csv_product.twig")
     *
     * @return array
     *
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Doctrine\ORM\NoResultException
     */
    public function csvProduct(Request $request, CacheUtil $cacheUtil)
    {
        $form = $this->formFactory->createBuilder(CsvImportType::class)->getForm();
        $headers = $this->getProductCsvHeader();

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $this->isSplitCsv = $form['is_split_csv']->getData();
                $this->csvFileNo = $form['csv_file_no']->getData();
                $formFile = $form['import_file']->getData();

                if (!empty($formFile)) {
                    $this->logger->info('商品CSV登録開始');

                    $data = $this->getImportData($formFile);
                    if ($data === false) {
                        $this->addErrors(trans('admin.common.csv_invalid_format'));
                        return $this->renderWithError($form, $headers, false);
                    }

                    $getId = fn($item) => $item['id'];
                    $requireHeader = array_keys(array_map($getId, array_filter($headers, fn($value) => $value['required'])));
                    $columnHeaders = $data->getColumnHeaders();

                    if (count(array_diff($requireHeader, $columnHeaders)) > 0) {
                        $this->addErrors(trans('admin.common.csv_invalid_format'));
                        return $this->renderWithError($form, $headers, false);
                    }

                    if (count($data) < 1) {
                        $this->addErrors(trans('admin.common.csv_invalid_no_data'));
                        return $this->renderWithError($form, $headers, false);
                    }

                    $headerByKey = array_flip(array_map($getId, $headers));
                    $deleteImages = [];

                    $this->entityManager->getConfiguration()->setSQLLogger(null);
                    $this->entityManager->getConnection()->beginTransaction();

                    /** @var \Eccube\Entity\ProductClass[] $ProductClasses */
                    foreach ($data as $row) {
                        $line = $data->key() + 1;
                        $this->currentLineNo = $line;

                        if (count($row) !== count($columnHeaders)) {
                            $this->addErrors(trans('admin.common.csv_invalid_format_line', ['%line%' => $line]));
                            return $this->renderWithError($form, $headers);
                        }

                        // --- Product 作成/取得 ---
                        $productStatus = $this->productStatusRepository->find(1);
                        $productClass = $this->productClassRepository->findOneBy(['code' => $row[$headerByKey['product_code']]]);
                        if (!$productClass) {
                            $Product = new Product();
                            $Product->setStatus($productStatus);
                            $this->entityManager->persist($Product);
                        } else {
                            $Product = $this->productRepository->find($productClass->getProduct()->getId());
                        }

                        // --- Product 情報設定 ---
                        if (StringUtil::isBlank($row[$headerByKey['name']])) {
                            $this->addErrors(trans('admin.common.csv_invalid_required', ['%line%' => $line, '%name%' => $headerByKey['name']]));
                            return $this->renderWithError($form, $headers);
                        }
                        $Product->setName(StringUtil::trimAll($row[$headerByKey['name']]));

                        if (!empty($row[$headerByKey['description_list']])) {
                            $Product->setDescriptionList($this->purifier->purify(StringUtil::trimAll($row[$headerByKey['description_list']])));
                        }

                        if (!empty($row[$headerByKey['description_detail']])) {
                            if (mb_strlen($row[$headerByKey['description_detail']]) > $this->eccubeConfig['eccube_ltext_len']) {
                                $this->addErrors(trans('admin.common.csv_invalid_description_detail_upper_limit', [
                                    '%line%' => $line, '%name%' => $headerByKey['description_detail'], '%max%' => $this->eccubeConfig['eccube_ltext_len']
                                ]));
                                return $this->renderWithError($form, $headers);
                            }
                            $Product->setDescriptionDetail($this->purifier->purify(StringUtil::trimAll($row[$headerByKey['description_detail']])));
                        }

                        if (!empty($row[$headerByKey['search_word']])) {
                            $Product->setSearchWord(StringUtil::trimAll($row[$headerByKey['search_word']]));
                        }

                        if (!empty($row[$headerByKey['free_area']])) {
                            $Product->setFreeArea($this->purifier->purify(StringUtil::trimAll($row[$headerByKey['free_area']])));
                        }

                        $Product->setMakerId($row[$headerByKey['maker_id']]);
                        $Product->setItemWeight($row[$headerByKey['item_weight']]);

                        $this->entityManager->flush();

                        // --- カテゴリ/タグ/画像登録 ---
                        $this->createProductCategory($row, $Product, $data, $headerByKey);
                        $this->createProductTag($row, $Product, $data, $headerByKey);
                        $this->createProductImage($row, $Product, $data, $headerByKey, $deleteImages);

                        // --- ProductClass 登録/更新 ---
                        $ProductClasses = $Product->getProductClasses();
                        if ($ProductClasses->count() < 1) {
                            $this->createProductClass($row, $Product, $data, $headerByKey);
                        } else {
                            $this->updateProductClass($row, $Product, $productClass, $data, $headerByKey);
                        }

                        if ($this->hasErrors()) {
                            return $this->renderWithError($form, $headers);
                        }

                        $this->entityManager->persist($Product);
                    }

                    $this->entityManager->flush();
                    $this->entityManager->getConnection()->commit();

                    // --- 画像削除 ---
                    foreach ($deleteImages as $images) {
                        foreach ($images as $image) {
                            try {
                                $fs = new Filesystem();
                                $fs->remove($this->eccubeConfig['eccube_save_image_dir'] . '/' . $image);
                            } catch (\Exception $e) {
                                // 無視
                            }
                        }
                    }

                    $this->logger->info('商品CSV登録完了');
                    $this->session->getFlashBag()->add('eccube.admin.success', 'admin.common.csv_upload_complete');
                    $cacheUtil->clearDoctrineCache();
                }
            }
        }

        return $this->renderWithError($form, $headers);
    }

    /**
     * カテゴリ登録CSVアップロード
     *
     * @Route("/%eccube_admin_route%/product/category_csv_upload", name="admin_product_category_csv_import", methods={"GET", "POST"})
     *
     * @Template("@admin/Product/csv_category.twig")
     */
    public function csvCategory(Request $request, CacheUtil $cacheUtil)
    {
        $form = $this->formFactory->createBuilder(CsvImportType::class)->getForm();

        $headers = $this->getCategoryCsvHeader();
        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $formFile = $form['import_file']->getData();
                if (!empty($formFile)) {
                    $this->logger->info('カテゴリCSV登録開始');
                    $data = $this->getImportData($formFile);
                    if ($data === false) {
                        $this->addErrors(trans('admin.common.csv_invalid_format'));

                        return $this->renderWithError($form, $headers, false);
                    }

                    $getId = function ($item) {
                        return $item['id'];
                    };
                    $requireHeader = array_keys(array_map($getId, array_filter($headers, function ($value) {
                        return $value['required'];
                    })));

                    $headerByKey = array_flip(array_map($getId, $headers));

                    $columnHeaders = $data->getColumnHeaders();
                    if (count(array_diff($requireHeader, $columnHeaders)) > 0) {
                        $this->addErrors(trans('admin.common.csv_invalid_format'));

                        return $this->renderWithError($form, $headers, false);
                    }

                    $size = count($data);
                    if ($size < 1) {
                        $this->addErrors(trans('admin.common.csv_invalid_no_data'));

                        return $this->renderWithError($form, $headers, false);
                    }
                    $this->entityManager->getConfiguration()->setSQLLogger(null);
                    $this->entityManager->getConnection()->beginTransaction();
                    // CSVファイルの登録処理
                    foreach ($data as $row) {
                        /** @var Category $Category */
                        $Category = new Category();
                        if (isset($row[$headerByKey['id']]) && strlen($row[$headerByKey['id']]) > 0) {
                            if (!preg_match('/^\d+$/', $row[$headerByKey['id']])) {
                                $this->addErrors(($data->key() + 1).'行目の親カテゴリIDは数字で入力してください。');

                                return $this->renderWithError($form, $headers);
                            }
                            $Category = $this->categoryRepository->find($row[$headerByKey['id']]);
                            if (!$Category) {
                                $this->addErrors(($data->key() + 1).'行目の更新対象のカテゴリIDが存在しません。新規登録の場合は、カテゴリIDの値を空で登録してください。');

                                return $this->renderWithError($form, $headers);
                            }
                            if ($row[$headerByKey['id']] == $row[$headerByKey['parent_category_id']]) {
                                $this->addErrors(($data->key() + 1).'行目のカテゴリIDと親カテゴリIDが同じです。');

                                return $this->renderWithError($form, $headers);
                            }
                        }

                        if (isset($row[$headerByKey['category_del_flg']]) && StringUtil::isNotBlank($row[$headerByKey['category_del_flg']])) {
                            if (StringUtil::trimAll($row[$headerByKey['category_del_flg']]) == 1) {
                                if ($Category->getId()) {
                                    $this->logger->info('カテゴリ削除開始', [$Category->getId()]);
                                    try {
                                        $this->categoryRepository->delete($Category);
                                        $this->logger->info('カテゴリ削除完了', [$Category->getId()]);
                                    } catch (ForeignKeyConstraintViolationException $e) {
                                        $this->logger->info('カテゴリ削除エラー', [$Category->getId(), $e]);
                                        $message = trans('admin.common.delete_error_foreign_key', ['%name%' => $Category->getName()]);
                                        $this->addError($message, 'admin');

                                        return $this->renderWithError($form, $headers);
                                    }
                                }

                                continue;
                            }
                        }

                        if (!isset($row[$headerByKey['category_name']]) || StringUtil::isBlank($row[$headerByKey['category_name']])) {
                            $this->addErrors(($data->key() + 1).'行目のカテゴリ名が設定されていません。');

                            return $this->renderWithError($form, $headers);
                        } else {
                            $Category->setName(StringUtil::trimAll($row[$headerByKey['category_name']]));
                        }

                        $ParentCategory = null;
                        if (isset($row[$headerByKey['parent_category_id']]) && StringUtil::isNotBlank($row[$headerByKey['parent_category_id']])) {
                            if (!preg_match('/^\d+$/', $row[$headerByKey['parent_category_id']])) {
                                $this->addErrors(($data->key() + 1).'行目の親カテゴリIDが存在しません。');

                                return $this->renderWithError($form, $headers);
                            }

                            /** @var Category $ParentCategory */
                            $ParentCategory = $this->categoryRepository->find($row[$headerByKey['parent_category_id']]);
                            if (!$ParentCategory) {
                                $this->addErrors(($data->key() + 1).'行目の親カテゴリIDが存在しません。');

                                return $this->renderWithError($form, $headers);
                            }
                        }
                        $Category->setParent($ParentCategory);

                        // Level
                        if (isset($row['階層']) && StringUtil::isNotBlank($row['階層'])) {
                            if ($ParentCategory == null && $row['階層'] != 1) {
                                $this->addErrors(($data->key() + 1).'行目の親カテゴリIDが存在しません。');

                                return $this->renderWithError($form, $headers);
                            }
                            $level = StringUtil::trimAll($row['階層']);
                        } else {
                            $level = 1;
                            if ($ParentCategory) {
                                $level = $ParentCategory->getHierarchy() + 1;
                            }
                        }

                        $Category->setHierarchy($level);

                        if ($this->eccubeConfig['eccube_category_nest_level'] < $Category->getHierarchy()) {
                            $this->addErrors(($data->key() + 1).'行目のカテゴリが最大レベルを超えているため設定できません。');

                            return $this->renderWithError($form, $headers);
                        }

                        if ($this->hasErrors()) {
                            return $this->renderWithError($form, $headers);
                        }
                        $this->entityManager->persist($Category);
                        $this->categoryRepository->save($Category);
                    }

                    $this->entityManager->getConnection()->commit();
                    $this->logger->info('カテゴリCSV登録完了');
                    $message = 'admin.common.csv_upload_complete';
                    $this->session->getFlashBag()->add('eccube.admin.success', $message);

                    $cacheUtil->clearDoctrineCache();
                }
            }
        }

        return $this->renderWithError($form, $headers);
    }

    /**
     * 規格登録CSVアップロード
     *
     * @Route("/%eccube_admin_route%/product/class_name_csv_upload", name="admin_product_class_name_csv_import", methods={"GET", "POST"})
     *
     * @Template("@admin/Product/csv_class_name.twig")
     */
    public function csvClassName(Request $request, CacheUtil $cacheUtil)
    {
        $form = $this->formFactory->createBuilder(CsvImportType::class)->getForm();

        $headers = $this->getClassNameCsvHeader();
        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $formFile = $form['import_file']->getData();
                if (!empty($formFile)) {
                    log_info('規格CSV登録開始');
                    $data = $this->getImportData($formFile);
                    if ($data === false) {
                        $this->addErrors(trans('admin.common.csv_invalid_format'));

                        return $this->renderWithError($form, $headers, false);
                    }

                    $getId = function ($item) {
                        return $item['id'];
                    };
                    $requireHeader = array_keys(array_map($getId, array_filter($headers, function ($value) {
                        return $value['required'];
                    })));

                    $headerByKey = array_flip(array_map($getId, $headers));

                    $columnHeaders = $data->getColumnHeaders();
                    if (count(array_diff($requireHeader, $columnHeaders)) > 0) {
                        $this->addErrors(trans('admin.common.csv_invalid_format'));

                        return $this->renderWithError($form, $headers, false);
                    }

                    $size = count($data);
                    if ($size < 1) {
                        $this->addErrors(trans('admin.common.csv_invalid_no_data'));

                        return $this->renderWithError($form, $headers, false);
                    }
                    $this->entityManager->getConfiguration()->setSQLLogger(null);
                    $this->entityManager->getConnection()->beginTransaction();
                    // CSVファイルの登録処理
                    foreach ($data as $row) {
                        // dump($row,$headerByKey);exit;
                        /** @var $ClassName ClassName */
                        $ClassName = new ClassName();
                        if (isset($row[$headerByKey['id']]) && strlen($row[$headerByKey['id']]) > 0) {
                            if (!preg_match('/^\d+$/', $row[$headerByKey['id']])) {
                                $this->addErrors(($data->key() + 1).'行目の規格IDが存在しません。');

                                return $this->renderWithError($form, $headers);
                            }
                            $ClassName = $this->classNameRepository->find($row[$headerByKey['id']]);
                            if (!$ClassName) {
                                $this->addErrors(($data->key() + 1).'行目の更新対象の規格IDが存在しません。新規登録の場合は、規格IDの値を空で登録してください。');

                                return $this->renderWithError($form, $headers);
                            }
                        }

                        if (isset($row[$headerByKey['class_name_del_flg']]) && StringUtil::isNotBlank($row[$headerByKey['class_name_del_flg']])) {
                            if (StringUtil::trimAll($row[$headerByKey['class_name_del_flg']]) == 1) {
                                if ($ClassName->getId()) {
                                    log_info('規格削除開始', [$ClassName->getId()]);
                                    try {
                                        $this->classNameRepository->delete($ClassName);
                                        log_info('規格削除完了', [$ClassName->getId()]);
                                    } catch (ForeignKeyConstraintViolationException $e) {
                                        log_info('規格削除エラー', [$ClassName->getId(), $e]);
                                        $message = trans('admin.common.delete_error_foreign_key', ['%name%' => $ClassName->getName()]);
                                        $this->addError($message, 'admin');

                                        return $this->renderWithError($form, $headers);
                                    }
                                }

                                continue;
                            }
                        }

                        if (!isset($row[$headerByKey['name']]) || StringUtil::isBlank($row[$headerByKey['name']])) {
                            $this->addErrors(($data->key() + 1).'行目規格名が設定されていません。');

                            return $this->renderWithError($form, $headers);
                        } else {
                            $ClassName->setName(StringUtil::trimAll($row[$headerByKey['name']]));
                        }

                        if (isset($row[$headerByKey['backend_name']]) && StringUtil::isNotBlank($row[$headerByKey['backend_name']])) {
                            $ClassName->setBackendName(StringUtil::trimAll($row[$headerByKey['backend_name']]));
                        }

                        if ($this->hasErrors()) {
                            return $this->renderWithError($form, $headers);
                        }
                        $this->entityManager->persist($ClassName);
                        $this->classNameRepository->save($ClassName);
                    }

                    $this->entityManager->getConnection()->commit();
                    log_info('規格CSV登録完了');
                    $message = 'admin.common.csv_upload_complete';
                    $this->session->getFlashBag()->add('eccube.admin.success', $message);

                    $cacheUtil->clearDoctrineCache();
                }
            }
        }

        return $this->renderWithError($form, $headers);
    }

    /**
     * 規格分類CSV登録CSVアップロード
     *
     * @Route("/%eccube_admin_route%/product/class_category_csv_upload", name="admin_product_class_category_csv_import", methods={"GET", "POST"})
     *
     * @Template("@admin/Product/csv_class_category.twig")
     */
    public function csvClassCategory(Request $request, CacheUtil $cacheUtil)
    {
        $form = $this->formFactory->createBuilder(CsvImportType::class)->getForm();

        $headers = $this->getClassCategoryCsvHeader();
        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $formFile = $form['import_file']->getData();
                if (!empty($formFile)) {
                    log_info('規格分類CSV登録開始');
                    $data = $this->getImportData($formFile);
                    if ($data === false) {
                        $this->addErrors(trans('admin.common.csv_invalid_format'));

                        return $this->renderWithError($form, $headers, false);
                    }

                    $getId = function ($item) {
                        return $item['id'];
                    };
                    $requireHeader = array_keys(array_map($getId, array_filter($headers, function ($value) {
                        return $value['required'];
                    })));

                    $headerByKey = array_flip(array_map($getId, $headers));

                    $columnHeaders = $data->getColumnHeaders();
                    if (count(array_diff($requireHeader, $columnHeaders)) > 0) {
                        $this->addErrors(trans('admin.common.csv_invalid_format'));

                        return $this->renderWithError($form, $headers, false);
                    }

                    $size = count($data);
                    if ($size < 1) {
                        $this->addErrors(trans('admin.common.csv_invalid_no_data'));

                        return $this->renderWithError($form, $headers, false);
                    }
                    $this->entityManager->getConfiguration()->setSQLLogger(null);
                    $this->entityManager->getConnection()->beginTransaction();
                    // CSVファイルの登録処理
                    foreach ($data as $row) {
                        // dump($row,$headerByKey);exit;
                        /** @var $ClassCategory ClassCategory */
                        $ClassCategory = new ClassCategory();

                        if (isset($row[$headerByKey['id']]) && strlen($row[$headerByKey['id']]) > 0) {
                            if (!preg_match('/^\d+$/', $row[$headerByKey['id']])) {
                                $this->addErrors(($data->key() + 1).'行目の規格分類IDが存在しません。');

                                return $this->renderWithError($form, $headers);
                            }
                            $ClassCategory = $this->classCategoryRepository->find($row[$headerByKey['id']]);
                            if (!$ClassCategory) {
                                $this->addErrors(($data->key() + 1).'行目の更新対象の規格分類IDが存在しません。新規登録の場合は、規格分類IDの値を空で登録してください。');

                                return $this->renderWithError($form, $headers);
                            }
                        }

                        if (isset($row[$headerByKey['class_name_id']]) && strlen($row[$headerByKey['class_name_id']]) > 0) {
                            if (!preg_match('/^\d+$/', $row[$headerByKey['class_name_id']])) {
                                $this->addErrors(($data->key() + 1).'行目の規格IDが存在しません。');

                                return $this->renderWithError($form, $headers);
                            }
                            $ClassName = $this->classNameRepository->find($row[$headerByKey['class_name_id']]);
                            if (!$ClassName) {
                                $this->addErrors(($data->key() + 1).'行目の更新対象の規格IDが存在しません。');

                                return $this->renderWithError($form, $headers);
                            }
                            $ClassCategory->setClassName($ClassName);
                        }

                        if (isset($row[$headerByKey['class_category_del_flg']]) && StringUtil::isNotBlank($row[$headerByKey['class_category_del_flg']])) {
                            if (StringUtil::trimAll($row[$headerByKey['class_category_del_flg']]) == 1) {
                                if ($ClassCategory->getId()) {
                                    log_info('規格分類削除開始', [$ClassCategory->getId()]);
                                    try {
                                        $this->classCategoryRepository->delete($ClassCategory);
                                        log_info('規格分類削除完了', [$ClassCategory->getId()]);
                                    } catch (ForeignKeyConstraintViolationException $e) {
                                        log_info('規格分類削除エラー', [$ClassCategory->getId(), $e]);
                                        $message = trans('admin.common.delete_error_foreign_key', ['%name%' => $ClassCategory->getName()]);
                                        $this->addError($message, 'admin');

                                        return $this->renderWithError($form, $headers);
                                    }
                                }

                                continue;
                            }
                        }

                        if (!isset($row[$headerByKey['name']]) || StringUtil::isBlank($row[$headerByKey['name']])) {
                            $this->addErrors(($data->key() + 1).'行目規格分類名が設定されていません。');

                            return $this->renderWithError($form, $headers);
                        } else {
                            $ClassCategory->setName(StringUtil::trimAll($row[$headerByKey['name']]));
                        }

                        if (isset($row[$headerByKey['backend_name']]) && StringUtil::isNotBlank($row[$headerByKey['backend_name']])) {
                            $ClassCategory->setBackendName(StringUtil::trimAll($row[$headerByKey['backend_name']]));
                        }

                        if ($this->hasErrors()) {
                            return $this->renderWithError($form, $headers);
                        }
                        $this->entityManager->persist($ClassCategory);
                        $this->classCategoryRepository->save($ClassCategory);
                    }

                    $this->entityManager->getConnection()->commit();
                    log_info('規格分類CSV登録完了');
                    $message = 'admin.common.csv_upload_complete';
                    $this->session->getFlashBag()->add('eccube.admin.success', $message);

                    $cacheUtil->clearDoctrineCache();
                }
            }
        }

        return $this->renderWithError($form, $headers);
    }

    /**
     * アップロード用CSV雛形ファイルダウンロード
     *
     * @Route("/%eccube_admin_route%/product/csv_template/{type}", requirements={"type" = "\w+"}, name="admin_product_csv_template", methods={"GET"})
     *
     * @param $type
     *
     * @return StreamedResponse
     */
    public function csvTemplate(Request $request, $type)
    {
        if ($type == 'product') {
            set_time_limit(0);

            $em = $this->entityManager;
            $em->getConfiguration()->setSQLLogger(null);

            $headers = [
                '商品コード', '商品名', '商品説明(一覧)', '商品説明(詳細)', '検索ワード',
                '商品画像', '商品カテゴリ(ID)', '在庫数(-1で無制限）', '通常価格', '販売価格',
                'JANｺｰﾄﾞ(13桁）', '仕入価格（税抜）', '仕入先コード(4桁)', '重量（kg）', 'メーカーID'
            ];
            $results = $this->productClassRepository->findAll();

            $response = new StreamedResponse();
            $response->setCallback(
                function () use ($results, $headers) {
                    $handle = fopen('php://output', 'r+');

                    fputcsv($handle, $headers);

                    foreach ($results as $row) {
                        $imgs = [];
                        if ($row->getProduct() && count($row->getProduct()->getProductImage())) {
                            $images = $row->getProduct()->getProductImage();
                            foreach ($images as $image) {
                                $imgs[] = $image->getFileName();
                            }
                        }
                        $cats = [];
                        if ($row->getProduct() && count($row->getProduct()->getProductCategories())) {
                            $categories = $row->getProduct()->getProductCategories();
                            foreach ($categories as $category) {
                                $cats[] = $category->getCategoryId();
                            }
                        }
                        $data = [
                            $row->getCode() ?? '',
                            $row->getProduct()->getName() ?? '',
                            $row->getProduct()->getDescriptionList() ?? '',
                            $row->getProduct()->getDescriptionDetail() ?? '',
                            $row->getProduct()->getSearchWord() ?? '',
                            join(',', $imgs),
                            join(',', $cats),
                            $row->getStock() === null ? -1 : $row->getStock(),
                            $row->getPrice01() ?? '',
                            $row->getPrice02() ?? '',
                            $row->getJanCode() ?? '',
                            $row->getItemCost() ?? '',
                            $row->getSupplierCode() ?? '',
                            $row->getProduct()->getItemWeight() ?? '',
                            $row->getProduct()->gettMakerId() ?? ''
                        ];
                        fputcsv($handle, $data);
                    }
                    fclose($handle);
                }
            );
            $response->headers->set('Content-Type', 'application/force-download');
            $response->headers->set('Content-Disposition', 'attachment; filename="product.csv"');

            return $response;
        } elseif ($type == 'category') {
            $headers = $this->getCategoryCsvHeader();
            $filename = 'category.csv';
        } else {
            throw new NotFoundHttpException();
        }

        return $this->sendTemplateResponse($request, array_keys($headers), $filename);
    }

    /**
     * 登録、更新時のエラー画面表示
     *
     * @param FormInterface $form
     * @param array $headers
     * @param bool $rollback
     *
     * @return array
     *
     * @throws \Doctrine\DBAL\ConnectionException
     */
    protected function renderWithError($form, $headers, $rollback = true)
    {
        if ($this->hasErrors()) {
            if ($rollback) {
                $this->entityManager->getConnection()->rollback();
            }
        }

        $this->removeUploadedFile();

        if ($this->isSplitCsv) {
            return $this->json([
                'success' => !$this->hasErrors(),
                'success_message' => trans('admin.common.csv_upload_line_success', [
                    '%from%' => $this->convertLineNo(2),
                    '%to%' => $this->currentLineNo, ]),
                'errors' => $this->errors,
                'error_message' => trans('admin.common.csv_upload_line_error', [
                    '%from%' => $this->convertLineNo(2), ]),
            ]);
        }
    }

    /**
     * 商品画像の削除、登録
     *
     * @param $row
     * @param Product $Product
     * @param CsvImportService $data
     * @param $headerByKey
     */
    protected function createProductImage($row, Product $Product, $data, $headerByKey)
    {
        if (!isset($row[$headerByKey['product_image']])) {
            return;
        }
        if (StringUtil::isNotBlank($row[$headerByKey['product_image']])) {
            // 画像の削除
            $ProductImages = $Product->getProductImage();
            foreach ($ProductImages as $ProductImage) {
                $Product->removeProductImage($ProductImage);
                $this->entityManager->remove($ProductImage);
            }

            // 画像の登録
            $images = explode(',', $row[$headerByKey['product_image']]);

            $sortNo = 1;

            $pattern = "/\\$|^.*.\.\\\.*|\/$|^.*.\.\/\.*/";
            foreach ($images as $image) {
                $fileName = StringUtil::trimAll($image);

                // 商品画像名のフォーマットチェック
                if (strlen($fileName) > 0 && preg_match($pattern, $fileName)) {
                    $message = trans('admin.common.csv_invalid_image', ['%line%' => $data->key() + 1, '%name%' => $headerByKey['product_image']]);
                    $this->addErrors($message);
                } else {
                    // 空文字は登録対象外
                    if (!empty($fileName)) {
                        $ProductImage = new ProductImage();
                        $ProductImage->setFileName($fileName);
                        $ProductImage->setProduct($Product);
                        $ProductImage->setSortNo($sortNo);

                        $Product->addProductImage($ProductImage);
                        $sortNo++;
                        $this->entityManager->persist($ProductImage);
                    }
                }
            }
        }
    }

    /**
     * 商品カテゴリの削除、登録
     *
     * @param $row
     * @param Product $Product
     * @param CsvImportService $data
     * @param $headerByKey
     */
    protected function createProductCategory($row, Product $Product, $data, $headerByKey)
    {
        if (!isset($row[$headerByKey['product_category']])) {
            return;
        }
        // カテゴリの削除
        $ProductCategories = $Product->getProductCategories();
        foreach ($ProductCategories as $ProductCategory) {
            $Product->removeProductCategory($ProductCategory);
            $this->entityManager->remove($ProductCategory);
            $this->entityManager->flush();
        }

        if (StringUtil::isNotBlank($row[$headerByKey['product_category']])) {
            // カテゴリの登録
            $categories = explode(',', $row[$headerByKey['product_category']]);
            $sortNo = 1;
            $categoriesIdList = [];
            foreach ($categories as $category) {
                $line = $data->key() + 1;
                if (preg_match('/^\d+$/', $category)) {
                    $Category = $this->categoryRepository->find($category);
                    if (!$Category) {
                        $message = trans('admin.common.csv_invalid_not_found_target', [
                            '%line%' => $line,
                            '%name%' => $headerByKey['product_category'],
                            '%target_name%' => $category,
                        ]);
                        $this->addErrors($message);
                    } else {
                        foreach ($Category->getPath() as $ParentCategory) {
                            if (!isset($categoriesIdList[$ParentCategory->getId()])) {
                                $ProductCategory = $this->makeProductCategory($Product, $ParentCategory, $sortNo);
                                $this->entityManager->persist($ProductCategory);
                                $sortNo++;

                                $Product->addProductCategory($ProductCategory);
                                $categoriesIdList[$ParentCategory->getId()] = true;
                            }
                        }
                        if (!isset($categoriesIdList[$Category->getId()])) {
                            $ProductCategory = $this->makeProductCategory($Product, $Category, $sortNo);
                            $sortNo++;
                            $this->entityManager->persist($ProductCategory);
                            $Product->addProductCategory($ProductCategory);
                            $categoriesIdList[$Category->getId()] = true;
                        }
                    }
                } else {
                    $message = trans('admin.common.csv_invalid_not_found_target', [
                        '%line%' => $line,
                        '%name%' => $headerByKey['product_category'],
                        '%target_name%' => $category,
                    ]);
                    $this->addErrors($message);
                }
            }
        }
    }

    /**
     * タグの登録
     *
     * @param array $row
     * @param Product $Product
     * @param CsvImportService $data
     */
    protected function createProductTag($row, Product $Product, $data, $headerByKey)
    {
        if (!isset($row[$headerByKey['product_tag']])) {
            return;
        }
        // タグの削除
        $ProductTags = $Product->getProductTag();
        foreach ($ProductTags as $ProductTag) {
            $Product->removeProductTag($ProductTag);
            $this->entityManager->remove($ProductTag);
        }

        if (StringUtil::isNotBlank($row[$headerByKey['product_tag']])) {
            // タグの登録
            $tags = explode(',', $row[$headerByKey['product_tag']]);
            foreach ($tags as $tag_id) {
                $Tag = null;
                if (preg_match('/^\d+$/', $tag_id)) {
                    $Tag = $this->tagRepository->find($tag_id);

                    if ($Tag) {
                        $ProductTags = new ProductTag();
                        $ProductTags
                            ->setProduct($Product)
                            ->setTag($Tag);

                        $Product->addProductTag($ProductTags);

                        $this->entityManager->persist($ProductTags);
                    }
                }
                if (!$Tag) {
                    $message = trans('admin.common.csv_invalid_not_found_target', [
                        '%line%' => $data->key() + 1,
                        '%name%' => $headerByKey['product_tag'],
                        '%target_name%' => $tag_id,
                    ]);
                    $this->addErrors($message);
                }
            }
        }
    }

    /**
     * 商品規格分類1、商品規格分類2がnullとなる商品規格情報を作成
     *
     * @param $row
     * @param Product $Product
     * @param CsvImportService $data
     * @param $headerByKey
     * @param null $ClassCategory1
     * @param null $ClassCategory2
     *
     * @return ProductClass
     */
    protected function createProductClassFromCsv($row, Product $Product, $data, $headerByKey, $ClassCategory1 = null, $ClassCategory2 = null)
    {
        // 1. ProductClass を作成（標準フロー）
        $ProductClass = new ProductClass();
        $ProductClass->setProduct($Product)
                    ->setVisible(true)
                    ->setClassCategory1($ClassCategory1)
                    ->setClassCategory2($ClassCategory2);

        $line = $data->key() + 1;

        // 2. CSV 固有のバリデーションと値設定
        $this->validateCsvProductClassRow($ProductClass, $row, $headerByKey, $line);

        // 3. ProductStock の作成（標準フロー）
        $ProductStock = new ProductStock();
        $ProductClass->setProductStock($ProductStock);
        $ProductStock->setProductClass($ProductClass);

        if (!$ProductClass->isStockUnlimited()) {
            $ProductStock->setStock($ProductClass->getStock());
        } else {
            $ProductStock->setStock(null);
        }

        // 4. Product に紐付け
        $Product->addProductClass($ProductClass);

        return $ProductClass;
    }

    /**
     * CSV 行ごとのバリデーションと値セット（完全版）
     */
    protected function validateCsvProductClassRow(ProductClass $ProductClass, array $row, array $headerByKey, int $line)
    {
        // --- 商品コード ---
        $ProductClass->setCode(!empty($row[$headerByKey['product_code']]) 
            ? StringUtil::trimAll($row[$headerByKey['product_code']]) 
            : null);

        // --- 商品原価 (itemCost) ---
        if (!empty($row[$headerByKey['item_cost']])) {
            /** @var \Eccube\Entity\ProductClass $ProductClass */
            $ProductClass->setItemCost($row[$headerByKey['item_cost']]);
        } else {
            $this->addErrors(trans('admin.common.csv_invalid_required', ['%line%' => $line, '%name%' => $headerByKey['item_cost']]));
        }

        // --- JANコード ---
        $ProductClass->setJanCode(!empty($row[$headerByKey['JAN_code']]) ? $row[$headerByKey['JAN_code']] : null);

        // --- 仕入先コード ---
        $ProductClass->setSupplierCode(!empty($row[$headerByKey['supplier_code']]) ? $row[$headerByKey['supplier_code']] : null);

        // --- 在庫 ---
        $stock = $row[$headerByKey['stock']] ?? '';
        if ($stock === '' || $stock < 0) {
            $ProductClass->setStockUnlimited(true);
            $ProductClass->setStock(null);
            if ($stock === '') {
                $this->addErrors(trans('admin.common.csv_invalid_required', ['%line%' => $line, '%name%' => $headerByKey['stock']]));
            }
        } else {
            $ProductClass->setStockUnlimited(false);
            $ProductClass->setStock($stock);
        }

        // --- 価格 ---
        foreach (['price01', 'price02'] as $priceKey) {
            if (!empty($row[$headerByKey[$priceKey]])) {
                $price = str_replace(',', '', $row[$headerByKey[$priceKey]]);
                $errors = $this->validator->validate($price, new GreaterThanOrEqual(['value' => 0]));
                if ($errors->count() === 0) {
                    $setter = 'set' . ucfirst($priceKey);
                    $ProductClass->$setter($price);
                } else {
                    $this->addErrors(trans('admin.common.csv_invalid_greater_than_zero', ['%line%' => $line, '%name%' => $headerByKey[$priceKey]]));
                }
            } else {
                $this->addErrors(trans('admin.common.csv_invalid_required', ['%line%' => $line, '%name%' => $headerByKey[$priceKey]]));
            }
        }

        // --- 配送期間固定 ---
        $deliveryDuration = $this->deliveryDurationRepository->findBy(['id' => 3]);
        if (!empty($deliveryDuration)) {
            $ProductClass->setDeliveryDuration($deliveryDuration[0]);
        }

        // --- その他必要な CSV カラム ---
        // 必要に応じて同様に追加可能
    }

    /**
     * 商品規格情報を更新
     *
     * @param $row
     * @param Product $Product
     * @param Object $ProductClass
     * @param CsvImportService $data
     *
     * @return ProductClass
     */
    protected function updateProductClass($row, Product $Product, Object $ProductClass, $data, $headerByKey)
    {
        $ProductClass->setProduct($Product);

        $line = $data->key() + 1;

        if (isset($row[$headerByKey['product_code']])) {
            if (StringUtil::isNotBlank($row[$headerByKey['product_code']])) {
                $ProductClass->setCode(StringUtil::trimAll($row[$headerByKey['product_code']]));
            } else {
                $ProductClass->setCode(null);
            }
        }

        if (isset($row[$headerByKey['sale_limit']])) {
            if ($row[$headerByKey['sale_limit']] != '') {
                $saleLimit = str_replace(',', '', $row[$headerByKey['sale_limit']]);
                if (preg_match('/^\d+$/', $saleLimit) && $saleLimit >= 0) {
                    $ProductClass->setSaleLimit($saleLimit);
                } else {
                    $message = trans('admin.common.csv_invalid_greater_than_zero', ['%line%' => $line, '%name%' => $headerByKey['sale_limit']]);
                    $this->addErrors($message);
                }
            } else {
                $ProductClass->setSaleLimit(null);
            }
        }

        if (isset($row[$headerByKey['item_cost']]) && StringUtil::isNotBlank($row[$headerByKey['item_cost']])) {
            $ProductClass->setItemCost($row[$headerByKey['item_cost']]);
        } else {
            $message = trans('admin.common.csv_invalid_required', ['%line%' => $line, '%name%' => $headerByKey['item_cost']]);
            $this->addErrors($message);
        }

        if (isset($row[$headerByKey['JAN_code']]) && StringUtil::isNotBlank($row[$headerByKey['JAN_code']])) {
            $ProductClass->setJanCode($row[$headerByKey['JAN_code']]);
        } else {
            $ProductClass->setJanCode(null);
        }

        if (isset($row[$headerByKey['supplier_code']]) && StringUtil::isNotBlank($row[$headerByKey['supplier_code']])) {
            $ProductClass->setSupplierCode($row[$headerByKey['supplier_code']]);
        } else {
            $ProductClass->setSupplierCode(null);
        }
        if (!isset($row[$headerByKey['stock']])
            || StringUtil::isBlank($row[$headerByKey['stock']])
            || str_replace(',', '', $row[$headerByKey['stock']]) <= -1
        ) {
            $ProductClass->setStockUnlimited(true);
            // 在庫数が設定されていなければエラー
            if ($row[$headerByKey['stock']] == '') {
                $message = trans('admin.common.csv_invalid_required', ['%line%' => $line, '%name%' => $headerByKey['stock']]);
                $this->addErrors($message);
            } else {
                $ProductClass->setStock(null);
            }
        } elseif ($row[$headerByKey['stock']] >= (string) Constant::DISABLED) {
            $ProductClass->setStockUnlimited(false);
            $ProductClass->setStock($row[$headerByKey['stock']]);
        } else {
            $message = trans('admin.common.csv_invalid_required', ['%line%' => $line, '%name%' => $headerByKey['stock_unlimited']]);
            $this->addErrors($message);
        }

        if (isset($row[$headerByKey['price01']])) {
            if ($row[$headerByKey['price01']] != '') {
                $price01 = str_replace(',', '', $row[$headerByKey['price01']]);
                $errors = $this->validator->validate($price01, new GreaterThanOrEqual(['value' => 0]));
                if ($errors->count() === 0) {
                    $ProductClass->setPrice01($price01);
                } else {
                    $message = trans('admin.common.csv_invalid_greater_than_zero', ['%line%' => $line, '%name%' => $headerByKey['price01']]);
                    $this->addErrors($message);
                }
            } else {
                $ProductClass->setPrice01(null);
            }
        }

        if ($row[$headerByKey['price02']] == '') {
            $message = trans('admin.common.csv_invalid_required', ['%line%' => $line, '%name%' => $headerByKey['price02']]);
            $this->addErrors($message);
        } else {
            $price02 = str_replace(',', '', $row[$headerByKey['price02']]);
            $errors = $this->validator->validate($price02, new GreaterThanOrEqual(['value' => 0]));
            if ($errors->count() === 0) {
                $ProductClass->setPrice02($price02);
            } else {
                $message = trans('admin.common.csv_invalid_greater_than_zero', ['%line%' => $line, '%name%' => $headerByKey['price02']]);
                $this->addErrors($message);
            }
        }

        $ProductStock = $ProductClass->getProductStock();

        // 在庫テーブルに存在しない場合、新規作成
        if (!$ProductStock instanceof ProductStock) {
            $ProductStock = new ProductStock();
            $ProductClass->setProductStock($ProductStock);
            $ProductStock->setProductClass($ProductClass);
        }

        if (!$ProductClass->isStockUnlimited()) {
            $ProductStock->setStock($ProductClass->getStock());
        } else {
            // 在庫無制限時はnullを設定
            $ProductStock->setStock(null);
        }

        if (isset($row[$headerByKey['product_class_visible_flg']])
            && StringUtil::isNotBlank($row[$headerByKey['product_class_visible_flg']])) {
            $ProductClass->setVisible((bool) $row[$headerByKey['product_class_visible_flg']]);
        }

        return $ProductClass;
    }

    /**
     * 登録、更新時のエラー画面表示
     */
    protected function addErrors($message)
    {
        $this->errors[] = $message;
    }

    /**
     * @return array
     */
    protected function getErrors()
    {
        return $this->errors;
    }

    /**
     * @return bool
     */
    protected function hasErrors()
    {
        return count($this->getErrors()) > 0;
    }

    /**
     * 商品登録CSVヘッダー定義
     *
     * @return array
     */
    protected function getProductCsvHeader()
    {
        return [
            trans('admin.product.product_csv.product_code_col') => [
                'id' => 'product_code',
                'description' => 'admin.product.product_csv.product_code_description',
                'required' => true,
            ],
            trans('admin.product.product_csv.product_name_col') => [
                'id' => 'name',
                'description' => 'admin.product.product_csv.product_name_description',
                'required' => true,
            ],
            trans('admin.product.product_csv.description_list_col') => [
                'id' => 'description_list',
                'description' => 'admin.product.product_csv.description_list_description',
                'required' => false,
            ],
            trans('admin.product.product_csv.description_detail_col') => [
                'id' => 'description_detail',
                'description' => 'admin.product.product_csv.description_detail_description',
                'required' => false,
            ],
            trans('admin.product.product_csv.keyword_col') => [
                'id' => 'search_word',
                'description' => 'admin.product.product_csv.keyword_description',
                'required' => false,
            ],
            trans('admin.product.product_csv.product_image_col') => [
                'id' => 'product_image',
                'description' => 'admin.product.product_csv.product_image_description',
                'required' => false,
            ],
            trans('admin.product.product_csv.category_col') => [
                'id' => 'product_category',
                'description' => 'admin.product.product_csv.category_description',
                'required' => false,
            ],
            trans('admin.product.product_csv.stock_col') => [
                'id' => 'stock',
                'description' => 'admin.product.product_csv.stock_description',
                'required' => true,
            ],
            trans('admin.product.product_csv.normal_price_col') => [
                'id' => 'price01',
                'description' => 'admin.product.product_csv.normal_price_description',
                'required' => false,
            ],
            trans('admin.product.product_csv.sale_price_col') => [
                'id' => 'price02',
                'description' => 'admin.product.product_csv.sale_price_description',
                'required' => true,
            ],
            trans('admin.product.product_csv.JAN_code_col') => [
                'id' => 'JAN_code',
                'description' => 'admin.product.product_csv.JAN_code_description',
                'required' => false,
            ],
            trans('admin.product.product_csv.item_cost_col') => [
                'id' => 'item_cost',
                'description' => 'admin.product.product_csv.item_cost_description',
                'required' => true,
            ],
            trans('admin.product.product_csv.supplier_code_col') => [
                'id' => 'supplier_code',
                'description' => 'admin.product.product_csv.supplier_code_description',
                'required' => false,
            ],
            trans('admin.product.product_csv.item_weight_col') => [
                'id' => 'item_weight',
                'description' => '',
                'required' => true,
            ],
            trans('admin.product.product_csv.maker_id_col') => [
                'id' => 'maker_id',
                'description' => 'admin.product.product_csv.maker_id_description',
                'required' => false,
            ],
            trans('admin.product.product_csv.product_class_visible_flag_col') => [
                'id' => 'product_class_visible_flg',
                'description' => 'admin.product.product_csv.product_class_visible_flag_description',
                'required' => false,
            ],
        ];
    }

    /**
     * カテゴリCSVヘッダー定義
     */
    protected function getCategoryCsvHeader()
    {
        return [
            trans('admin.product.category_csv.category_id_col') => [
                'id' => 'id',
                'description' => 'admin.product.category_csv.category_id_description',
                'required' => false,
            ],
            trans('admin.product.category_csv.category_name_col') => [
                'id' => 'category_name',
                'description' => 'admin.product.category_csv.category_name_description',
                'required' => true,
            ],
            trans('admin.product.category_csv.parent_category_id_col') => [
                'id' => 'parent_category_id',
                'description' => 'admin.product.category_csv.parent_category_id_description',
                'required' => false,
            ],
            trans('admin.product.category_csv.delete_flag_col') => [
                'id' => 'category_del_flg',
                'description' => 'admin.product.category_csv.delete_flag_description',
                'required' => false,
            ],
        ];
    }

    /**
     * 規格分類CSVヘッダー定義
     */
    protected function getClassCategoryCsvHeader()
    {
        return [
            trans('admin.product.class_category_csv.class_name_id_col') => [
                'id' => 'class_name_id',
                'description' => 'admin.product.class_category_csv.class_name_id_description',
                'required' => true,
            ],
            trans('admin.product.class_category_csv.class_category_id_col') => [
                'id' => 'id',
                'description' => 'admin.product.class_category_csv.class_category_id_description',
                'required' => false,
            ],
            trans('admin.product.class_category_csv.class_category_name_col') => [
                'id' => 'name',
                'description' => 'admin.product.class_category_csv.class_category_name_description',
                'required' => true,
            ],
            trans('admin.product.class_category_csv.class_category_backend_name_col') => [
                'id' => 'backend_name',
                'description' => 'admin.product.class_category_csv.class_category_backend_name_description',
                'required' => false,
            ],
            trans('admin.product.class_category_csv.delete_flag_col') => [
                'id' => 'class_category_del_flg',
                'description' => 'admin.product.class_category_csv.delete_flag_description',
                'required' => false,
            ],
        ];
    }

    /**
     * ProductCategory作成
     *
     * @param \Eccube\Entity\Product $Product
     * @param \Eccube\Entity\Category $Category
     * @param int $sortNo
     *
     * @return ProductCategory
     */
    private function makeProductCategory($Product, $Category, $sortNo)
    {
        $ProductCategory = new ProductCategory();
        $ProductCategory->setProduct($Product);
        $ProductCategory->setProductId($Product->getId());
        $ProductCategory->setCategory($Category);
        $ProductCategory->setCategoryId($Category->getId());

        return $ProductCategory;
    }

    /**
     * @Route("/%eccube_admin_route%/product/csv_split", name="admin_product_csv_split", methods={"POST"})
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function splitCsv(Request $request)
    {
        $this->isTokenValid();
        if (!$request->isXmlHttpRequest()) {
            throw new BadRequestHttpException();
        }
        $form = $this->formFactory->createBuilder(CsvImportType::class)->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $dir = $this->eccubeConfig['eccube_csv_temp_realdir'];
            if (!file_exists($dir)) {
                $fs = new Filesystem();
                $fs->mkdir($dir);
            }

            $data = $form['import_file']->getData();
            $file = new \SplFileObject($data->getRealPath());

            // stream filter を適用して文字エンコーディングと改行コードの変換を行う
            // see https://github.com/EC-CUBE/ec-cube/issues/5252
            $filters = [
                ConvertLineFeedFilter::class,
            ];

            if (!\mb_check_encoding($file->current(), 'UTF-8')) {
                // UTF-8 が検出できなかった場合は SJIS-win の stream filter を適用する
                $filters[] = SjisToUtf8EncodingFilter::class;
            }
            $src = CsvImportService::applyStreamFilter($file, ...$filters);
            $src->setFlags(\SplFileObject::READ_CSV | \SplFileObject::READ_AHEAD | \SplFileObject::SKIP_EMPTY);

            $fileNo = 1;
            $fileName = StringUtil::random(8);
            $dist = new \SplFileObject($dir.'/'.$fileName.$fileNo.'.csv', 'w');
            $header = $src->current();
            $src->next();
            $dist->fputcsv($header, ',', '"', '\\');

            $i = 0;
            while ($row = $src->current()) {
                $dist->fputcsv($row, ',', '"', '\\');
                $src->next();

                if (!$src->eof() && ++$i % $this->eccubeConfig['eccube_csv_split_lines'] === 0) {
                    $fileNo++;
                    $dist = new \SplFileObject($dir.'/'.$fileName.$fileNo.'.csv', 'w');
                    $dist->fputcsv($header, ',', '"', '\\');
                }
            }

            return $this->json(['success' => true, 'file_name' => $fileName, 'max_file_no' => $fileNo]);
        }

        return $this->json(['success' => false, 'message' => $form->getErrors(true, true)]);
    }    

/**
     * @Route("/%eccube_admin_route%/product/csv_split_import", name="admin_product_csv_split_import", methods={"POST"})
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function importCsv(Request $request, CsrfTokenManagerInterface $tokenManager)
    {
        $this->isTokenValid();
        if (!$request->isXmlHttpRequest()) {
            throw new BadRequestHttpException();
        }
        $choices = $this->getCsvTempFiles();
        $filename = $request->get('file_name');
        if (!isset($choices[$filename])) {
            throw new BadRequestHttpException();
        }
        $path = $this->eccubeConfig['eccube_csv_temp_realdir'].'/'.$filename;
        $request->files->set('admin_csv_import', ['import_file' => new UploadedFile(
            $path,
            'import.csv',
            'text/csv',
            null,
            true
        )]);
        $request->setMethod('POST');
        $request->request->set('admin_csv_import', [
            Constant::TOKEN_NAME => $tokenManager->getToken('admin_csv_import')->getValue(),
            'is_split_csv' => true,
            'csv_file_no' => $request->get('file_no'),
        ]);
        return $this->forwardToRoute('admin_product_csv_import');
    }

    /**
     * @Route("/%eccube_admin_route%/product/csv_split_cleanup", name="admin_product_csv_split_cleanup", methods={"POST"})
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function cleanupSplitCsv(Request $request)
    {
        $this->isTokenValid();
        if (!$request->isXmlHttpRequest()) {
            throw new BadRequestHttpException();
        }
        $files = $request->get('files', []);
        $choices = $this->getCsvTempFiles();
        foreach ($files as $filename) {
            if (isset($choices[$filename])) {
                unlink($choices[$filename]);
            } else {
                return $this->json(['success' => false]);
            }
        }
        return $this->json(['success' => true]);
    }
    protected function getCsvTempFiles()
    {
        $files = Finder::create()
            ->in($this->eccubeConfig['eccube_csv_temp_realdir'])
            ->name('*.csv')
            ->files();
        $choices = [];
        foreach ($files as $file) {
            $choices[$file->getBaseName()] = $file->getRealPath();
        }
        return $choices;
    }

    protected function convertLineNo($currentLineNo)
    {
        if ($this->isSplitCsv) {
            return $this->eccubeConfig['eccube_csv_split_lines'] * ($this->csvFileNo - 1) + $currentLineNo;
        }

        return $currentLineNo;
    }
}
