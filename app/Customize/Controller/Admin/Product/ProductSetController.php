<?php

namespace Customize\Controller\Admin\Product;

use Customize\Entity\ProductSet;
use Customize\Form\Type\Admin\ProductSetType;
use Customize\Repository\ProductSetRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Eccube\Controller\AbstractController;
use Eccube\Form\Type\Admin\SearchProductType;
use Eccube\Repository\CategoryRepository;
use Eccube\Repository\ProductClassRepository;
use Eccube\Repository\ProductRepository;
use Knp\Component\Pager\Paginator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Eccube\Form\Type\AddCartType;
use Symfony\Component\Form\FormFactoryInterface;
use Eccube\Common\EccubeConfig;
use Doctrine\ORM\EntityManagerInterface;

class ProductSetController extends AbstractController
{
    /**
     * @var ProductClassRepository
     */
    protected $productClassRepository;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var ProductSetRepository
     */
    protected $productSetRepository;

    /**
     * @var CategoryRepository
     */
    protected $categoryRepository;

    /**
     * @var EccubeConfig
     */
    protected $eccubeConfig;

    /**
     * ProductSetController constructor.
     *
     * @param ProductClassRepository $productClassRepository
     * @param ProductRepository $productRepository
     * @param ProductSetRepository $productSetRepository
     * @param CategoryRepository $categoryRepository
     */
    public function __construct(
        ProductClassRepository $productClassRepository,
        ProductRepository      $productRepository,
        ProductSetRepository   $productSetRepository,
        CategoryRepository     $categoryRepository,
        FormFactoryInterface $formFactory,
        EccubeConfig $eccubeConfig,
        EntityManagerInterface $entityManager
    ) {
        $this->productClassRepository = $productClassRepository;
        $this->productRepository = $productRepository;
        $this->productSetRepository = $productSetRepository;
        $this->categoryRepository = $categoryRepository;
        $this->eccubeConfig = $eccubeConfig;
        
        // 親クラスのsetterメソッドを呼び出してプロパティを設定
        $this->setFormFactory($formFactory);
        $this->setEntityManager($entityManager);
    }

    /**
     * セット商品管理画面
     *
     * @Route("/%eccube_admin_route%/product/set/{id}", name="admin_product_set")
     * @Template("@admin/Product/product_set.twig")
     */
    public function product_set(Request $request, $id)
    {
        $Product = $this->productRepository->find($id);
        $ProductClass = $this->productClassRepository->findOneBy(['Product' => $Product]);
        if (!$Product || !$ProductClass) {
            throw new NotFoundHttpException();
        }

        $Sets = $this->productSetRepository->findBy(['ParentProduct' => $Product]);
        $countSet = count($Sets);

        $OriginSets = new ArrayCollection();
        foreach ($Sets as $set) {
            $item = new ProductSet;
            $item->setId($set->getId());
            $item->setQuantity($set->getSetUnit());
            $item->setProduct($set->getProduct());
            $item->setPrice($set->getProductClass()->getPrice02());
            $item->setProductClass($set->getProductClass());
            $item->setProductName($set->getProduct()->getName());
            $OriginSets->add($item);
        }
        $Product->setProductSet();
        foreach ($OriginSets as $key => $item) {
            $Product->addProductSet($item);
        }

        $builder = $this->formFactory->createBuilder(ProductSetType::class, $Product);
        $form = $builder->getForm();
        $form->handleRequest($request);

        $builder = $this->formFactory->createBuilder(SearchProductType::class);
        $searchProductModalForm = $builder->getForm();

        if ($form->isSubmitted() && $form['ProductSet']->isValid()) {
            $items = $form['ProductSet']->getData();
            switch ($request->get('mode')) {
                case 'register':
                    if ($form->isValid()) {
                        $Product->setProductSet();
                        $idDb = [];
                        $idReq = [];
                        foreach ($this->productSetRepository->findBy(['ParentProduct' => $Product]) as $item) {
                            array_push($idDb, $item->getId());
                        }
                        foreach ($items as $key => $item) {
                            array_push($idReq, $item->getId());
                            if ($item->getId()) {
                                $ProductSet = $this->productSetRepository->find($item->getId());
                                $ProductSet->setSetUnit($item->getQuantity());
                            } else {
                                $ProductSet = (new ProductSet())
                                    ->setSetUnit($item->getQuantity())
                                    ->setParentProduct($Product)
                                    ->setParentProductClass($ProductClass)
                                    ->setProduct($item->getProductClass()->getProduct())
                                    ->setProductClass($item->getProductClass());
                            }
                            $this->entityManager->persist($ProductSet);
                        }
                        foreach ($idDb as $item) {
                            if (!in_array($item, $idReq)) {
                                $objDel = $this->productSetRepository->find($item);
                                $this->entityManager->remove($objDel);
                            }
                        }
                        $this->entityManager->flush();

                        $this->addSuccess('admin.common.save_complete', 'admin');
                        return $this->redirectToRoute('admin_product');
                    }
            }
        }

        return [
            'form' => $form->createView(),
            'searchProductModalForm' => $searchProductModalForm->createView(),
            'Product' => $Product,
            'ProductClass' => $ProductClass,
            'count' => $countSet
        ];
    }

    /**
     * Product set search product.
     * 
     * @Route("/%eccube_admin_route%/product/set/search/product", name="admin_product_set_search_product")
     * @Route("/%eccube_admin_route%/product/set/search/product/page/{page_no}", requirements={"page_no" = "\d+"}, name="admin_product_set_search_product_page")
     * @Template("@admin/Product/product_set_search_product.twig")
     */
    public function searchProduct(Request $request, $page_no = null, Paginator $paginator)
    {
        if ($request->isXmlHttpRequest() && $this->isTokenValid()) {
            log_debug('search product start.');
            $page_count = $this->eccubeConfig['eccube_default_page_count'];
            $session = $this->session;

            if ('POST' === $request->getMethod()) {
                $page_no = 1;

                $searchData = [
                    'id' => $request->get('id'),
                ];

                if ($categoryId = $request->get('category_id')) {
                    $Category = $this->categoryRepository->find($categoryId);
                    $searchData['category_id'] = $Category;
                }

                $session->set('eccube.admin.product.set.search.product', $searchData);
                $session->set('eccube.admin.product.set.search.product.page_no', $page_no);
            } else {
                $searchData = (array) $session->get('eccube.admin.product.set.search.product');
                if (is_null($page_no)) {
                    $page_no = intval($session->get('eccube.admin.product.set.search.product.page_no'));
                } else {
                    $session->set('eccube.admin.product.set.search.product.page_no', $page_no);
                }
            }

            $qb = $this->productRepository
                ->getQueryBuilderBySearchDataForAdmin($searchData);

            /** @var \Knp\Component\Pager\Pagination\SlidingPagination $pagination */
            $pagination = $paginator->paginate(
                $qb,
                $page_no,
                $page_count,
                ['wrap-queries' => true]
            );

            /** @var $Products \Eccube\Entity\Product[] */
            $Products = $pagination->getItems();

            if (empty($Products)) {
                log_debug('search product not found.');
            }

            $forms = [];
            foreach ($Products as $Product) {
                /* @var $builder \Symfony\Component\Form\FormBuilderInterface */
                $builder = $this->formFactory->createNamedBuilder('', AddCartType::class, null, [
                    'product' => $this->productRepository->findWithSortedClassCategories($Product->getId()),
                ]);
                $addCartForm = $builder->getForm();
                $forms[$Product->getId()] = $addCartForm->createView();
            }

            return [
                'forms' => $forms,
                'Products' => $Products,
                'pagination' => $pagination,
                'is_instock' => $request->get('is_instock') ?? 0,
                'reIndex' => $request->get('reIndex') ?? 0,
            ];
        }
    }
}
