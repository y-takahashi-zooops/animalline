<?php

namespace Plugin\ZooopsSubscription\Controller\Admin;

use Eccube\Controller\AbstractController;
use Plugin\ZooopsSubscription\Repository\SubscriptionContractRepository;
use Eccube\Repository\CustomerRepository;
use Eccube\Repository\Master\PageMaxRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;
use Plugin\ZooopsSubscription\Form\Type\Admin\SearchSubscriptionType;
use Symfony\Component\HttpFoundation\Request;
use Knp\Component\Pager\Paginator;
use Eccube\Util\FormUtil;
use Eccube\Common\EccubeConfig;

class SubscriptionController extends AbstractController
{
    /**
     * @var SubscriptionContractRepository
     */
    protected $subscriptionContractRepository;

    /**
     * @var PageMaxRepository
     */
    protected $pageMaxRepository;

    /**
     * @var CustomerRepository
     */
    protected $customerRepository;

    /**
     * SubscriptionController constructor.
     *
     * @param SubscriptionContractRepository $subscriptionContractRepository
     * @param PageMaxRepository $pageMaxRepository
     * @param CustomerRepository $customerRepository
     */
    public function __construct(
        SubscriptionContractRepository $subscriptionContractRepository,
        PageMaxRepository $pageMaxRepository,
        CustomerRepository $customerRepository,
        EccubeConfig $eccubeConfig
    ) {
        $this->subscriptionContractRepository = $subscriptionContractRepository;
        $this->pageMaxRepository = $pageMaxRepository;
        $this->customerRepository = $customerRepository;
        $this->eccubeConfig = $eccubeConfig;
    }

    /**
     * @Route("/%eccube_admin_route%/zooops_subscription_view", name="admin_zooops_subscription_view")
     * @Route("/%eccube_admin_route%/zooops_subscription_view/page/{page_no}", requirements={"page_no" = "\d+"}, name="admin_zooops_subscription_view_page")
     * @Template("@ZooopsSubscription/admin/subscription_view.twig")
     */
    public function index(Request $request, $page_no = null, Paginator $paginator)
    {
        // DBデータの取得
        $pageMaxis = $this->pageMaxRepository->findAll();
        $subscriptions = $this->subscriptionContractRepository->findAll();
        $customers = $this->customerRepository->findAll();

        $searchForm = $this->createForm(SearchSubscriptionType::class);

        // デフォルトのページカウントを50件に設定
        $page_count = $this->session->get('eccube.admin.subscription.search.page_count',
            $this->eccubeConfig->get('eccube_default_page_count'));

        // 現在選択されている表示件数を取得
        $page_count_param = (int) $request->get('page_count');

        if ($page_count_param) {
            foreach ($pageMaxis as $pageMax) {
                if ($page_count_param == $pageMax->getName()) {
                    $page_count = $pageMax->getName();
                    $this->session->set('eccube.admin.subscription.search.page_count', $page_count);
                    break;
                }
            }
        }

        if ('POST' === $request->getMethod()) {
            $searchForm->handleRequest($request);

            if ($searchForm->isValid()) {
                /**
                 * 検索が実行された場合は, セッションに検索条件を保存する.
                 * ページ番号は最初のページ番号に初期化する.
                 */
                $page_no = 1;
                $searchData = $searchForm->getData();

                // 検索条件, ページ番号をセッションに保持.
                $this->session->set('eccube.admin.subscription.search', FormUtil::getViewData($searchForm));
                $this->session->set('eccube.admin.subscription.search.page_no', $page_no);
            }
        } else {
            if (null !== $page_no || $request->get('resume')) {
                /*
                 * ページ送りの場合または、他画面から戻ってきた場合は, セッションから検索条件を復旧する.
                 */
                if ($page_no) {
                    // ページ送りで遷移した場合.
                    $this->session->set('eccube.admin.subscription.search.page_no', (int) $page_no);
                } else {
                    // 他画面から遷移した場合.
                    $page_no = $this->session->get('eccube.admin.subscription.search.page_no', 1);
                }
                $viewData = $this->session->get('eccube.admin.subscription.search', []);
                $searchData = FormUtil::submitAndGetData($searchForm, $viewData);
            } else {
                /**
                 * 初期表示の場合.
                 */
                $page_no = 1;

                $viewData = FormUtil::getViewData($searchForm);
                $searchData = FormUtil::submitAndGetData($searchForm, $viewData);

                // セッション中の検索条件, ページ番号を初期化.
                $this->session->set('eccube.admin.subscription.search', FormUtil::getViewData($searchForm));
                $this->session->set('eccube.admin.subscription.search.page_no', $page_no);
 
            }
        }

        $qb = $this->subscriptionContractRepository->getSearchData($searchData);

        $pagination = $paginator->paginate(
            $qb,
            $page_no,
            $page_count
        );

        return [
            'searchForm' => $searchForm->createView(),
            'page_count' => $page_count,
            'pagination' => $pagination,
            'page_no' => $page_no,

            // DBデータ
            'pageMaxis' => $pageMaxis,
            'subscriptions' => $subscriptions,
            'customers' => $customers,
        ];
    }
}
