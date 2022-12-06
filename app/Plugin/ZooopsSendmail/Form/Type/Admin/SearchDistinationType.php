<?php

namespace Plugin\ZooopsSendmail\Form\Type\Admin;

use Eccube\Common\EccubeConfig;
use Symfony\Component\Form\AbstractType;
use Eccube\Form\Type\Master\CategoryType as MasterCategoryType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Eccube\Form\Type\PriceType;
use Symfony\Component\Form\FormBuilderInterface;
use Eccube\Repository\CategoryRepository;
use Eccube\Repository\ProductRepository;
use Plugin\ZooopsSendmail\Repository\MailTemplateRepository;
use Symfony\Component\Validator\Constraints as Assert;

class SearchDistinationType extends AbstractType
{
    /**
     * @var CategoryRepository
     */
    protected $categoryRepository;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var MailTemplateRepository
     */
    protected $templateRepository;

    /**
     * @var EccubeConfig
     */
    protected $eccubeConfig;

    /**
     * ProductType constructor.
     *
     * @param CategoryRepository $categoryRepository
     * @param ProductRepository $productRepository
     * @param MailTemplateRepository $templateRepository
     * @param EccubeConfig $eccubeConfig
     */
    public function __construct(
        CategoryRepository $categoryRepository,
        ProductRepository $productRepository,
        MailTemplateRepository $templateRepository,
        EccubeConfig $eccubeConfig
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->productRepository = $productRepository;
        $this->templateRepository = $templateRepository;
        $this->eccubeConfig = $eccubeConfig;
    }


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // 商品名プルダウン準備
        $products = $this->productRepository->findAll();

        foreach ($products as $product) {
            $productChoices[$product->getName()] = $product->getId();
        }

        $builder
            // カテゴリプルダウン 
            ->add('category_id', MasterCategoryType::class, [
                'label' => 'admin.product.category',
                'placeholder' => 'common.select__all_products',
                'required' => false,
                'multiple' => false,
                'expanded' => false,
                'choices' => $this->categoryRepository->getList(null, true),
            ])
            // 商品名プルダウン
            ->add('product_name', ChoiceType::class, [
                'label' => 'admin.product.name',
                'placeholder' => 'common.select__all_products',
                'required' => false,
                'multiple' => false,
                'expanded' => false,
                'choices' => $productChoices,

            ])
            // 最終購入日(開始)
            ->add('buy_date_start', DateType::class, [
                'label' => 'admin.order.last_buy_date__start',
                'required' => false,
                'input' => 'datetime',
                'widget' => 'single_text',
                'placeholder' => ['year' => '----', 'month' => '--', 'day' => '--'],
                'attr' => [
                    'class' => 'datetimepicker-input',
                    'data-target' => '#'.$this->getBlockPrefix().'_buy_date_start',
                    'data-toggle' => 'datetimepicker',
                ],
            ])
            // 最終購入日(終了)
            ->add('buy_date_end', DateType::class, [
                'label' => 'admin.order.next_delivery_date_end',
                'required' => false,
                'input' => 'datetime',
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'placeholder' => ['year' => '----', 'month' => '--', 'day' => '--'],
                'attr' => [
                    'class' => 'datetimepicker-input',
                    'data-target' => '#'.$this->getBlockPrefix().'_buy_date_end',
                    'data-toggle' => 'datetimepicker',
                ],
            ])
            // 購入金額(開始)
            ->add('buy_total_start', PriceType::class, [
                'label' => 'admin.order.purchase_price__start',
                'required' => false,
                'constraints' => [
                    new Assert\Length(['max' => $this->eccubeConfig['eccube_price_len']]),
                ],
            ])
            // 購入金額(終了)
            ->add('buy_total_end', PriceType::class, [
                'label' => 'admin.order.purchase_price__end',
                'required' => false,
                'constraints' => [
                    new Assert\Length(['max' => $this->eccubeConfig['eccube_price_len']]),
                ],
            ])
            // 購入件数(開始)
            ->add('buy_times_start', IntegerType::class, [
                'label' => 'admin.order.purchase_count__start',
                'required' => false,
                'constraints' => [
                    new Assert\Length(['max' => $this->eccubeConfig['eccube_int_len']]),
                ],
            ])
            // 購入件数(終了)
            ->add('buy_times_end', IntegerType::class, [
                'label' => 'admin.order.purchase_count__end',
                'required' => false,
                'constraints' => [
                    new Assert\Length(['max' => $this->eccubeConfig['eccube_int_len']]),
                ],
            ]);

            $entity = $builder->getData();

            // テンプレート選択状態
            $template_selector_default = array();
            if(isset($entity)){
                $template_selector_default = array($entity->getId());
            }
    
            // ドロップダウン準備
            $templates = $this->templateRepository->findAll();
    
            $choices = array('選択してください' => 0);
    
            foreach ($templates as $template) {
                $choices[$template->getTemplateName()] = $template->getId();
            }
    
            $builder->add('template_selector', ChoiceType::class, [
                'choices'  => $choices,
                'mapped' => false,
                'preferred_choices' => $template_selector_default,
            ]);
            // ドロップダウン準備
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'admin_search_distination';
    }
}
