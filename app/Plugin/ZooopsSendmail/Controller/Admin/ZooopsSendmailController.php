<?php

namespace Plugin\ZooopsSendmail\Controller\Admin;

use Eccube\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Eccube\Service\CsvExportService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;
use Eccube\Util\FormUtil;
use Plugin\ZooopsSendmail\Form\Type\Admin\MailTemplateType;
use Plugin\ZooopsSendmail\Form\Type\Admin\SearchDistinationType;
use Plugin\ZooopsSendmail\Repository\MailTemplateRepository;
use Eccube\Repository\CustomerRepository;
use Eccube\Repository\Master\PageMaxRepository;
use Plugin\ZooopsSendmail\Entity\MailTemplate;
use Customize\Service\SendMailProcess;
use Eccube\Common\EccubeConfig;

class ZooopsSendmailController extends AbstractController
{
    /**
     * @var MailTemplateRepository
     */
    protected $templateRepository;

    /**
     * @var PageMaxRepository
     */
    protected $pageMaxRepository;

    /**
     * @var CustomerRepository
     */
    protected $customerRepository;

    /**
     * @var CsvExportService
     */
    protected $csvExportService;

    /**
     * @var SendMailProcess
     */
    protected $sendMailProcess;

    /**
     * ZooopsSendmailController constructor.
     *
     * @param MailTemplateRepository $templateRepository
     * @param PageMaxRepository $pageMaxRepository
     * @param CustomerRepository $customerRepository
     * @param CsvExportService $csvExportService
     * @param SendMailProcess $SendMailProcess
     */
    public function __construct(
        MailTemplateRepository $templateRepository,
        PageMaxRepository $pageMaxRepository,
        CustomerRepository $customerRepository,
        CsvExportService $csvExportService,
        SendMailProcess $sendMailProcess,
        EccubeConfig $eccubeConfig
    ) {
        $this->templateRepository = $templateRepository;
        $this->pageMaxRepository = $pageMaxRepository;
        $this->customerRepository = $customerRepository;
        $this->csvExportService = $csvExportService;
        $this->sendMailProcess = $sendMailProcess;
        $this->eccubeConfig = $eccubeConfig;
    }

    /**
     * @Route("/%eccube_admin_route%/zooops_sendmail/template", name="admin_zooops_sendmail_template")
     * @Template("@ZooopsSendmail/admin/template.twig")
     */
    public function template(Request $request)
    {
        //メールテンプレート新規フォーム
        $form = $this->createForm(MailTemplateType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formdata = $form->getData();
            $this->sendMailProcess->registTemplate($formdata);
            $this->addSuccess('登録しました。', 'admin');
            return $this->redirectToRoute('admin_zooops_sendmail_template_edit', array('id' => $formdata->getId()));
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/%eccube_admin_route%/zooops_sendmail/template/{id}", name="admin_zooops_sendmail_template_edit")
     * @Template("@ZooopsSendmail/admin/template.twig")
     */
    public function template_edit(MailTemplate $template, Request $request)
    {
        //メールテンプレート更新フォーム
        $form = $this->createForm(MailTemplateType::class, $template);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $formdata = $form->getData();
            // 登録ボタンが押された場合、更新処理を行う
            if (isset($_POST['regist'])) {
                $this->sendMailProcess->registTemplate($formdata);
                $this->addSuccess('更新しました。', 'admin');
                return $this->redirectToRoute('admin_zooops_sendmail_template_edit', array('id' => $formdata->getId()));
            }
            // 削除ボタンが押された場合、削除処理を行う
            elseif (isset($_POST['delete'])) {
                $this->sendMailProcess->removeTemplate($formdata);
                $this->addSuccess('削除しました。', 'admin');
                // 当該ページが削除されていて存在しないため、初期ページにリダイレクト
                return $this->redirectToRoute('admin_zooops_sendmail_template');
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/%eccube_admin_route%/zooops_sendmail/send", name="admin_zooops_sendmail_send")
     * @Route("/%eccube_admin_route%/zooops_sendmail/send/page/{page_no}", requirements={"page_no" = "\d+"}, name="admin_zooops_sendmail_send_page")
     * @Template("@ZooopsSendmail/admin/send.twig")
     */
    public function index(Request $request, PaginatorInterface $paginator, ?int $page_no = null)
    {
        // DBデータの取得
        $pageMaxis = $this->pageMaxRepository->findAll();
        $customers = $this->customerRepository->findAll();
        $templates = $this->templateRepository->findAll();

        $searchForm = $this->createForm(SearchDistinationType::class);
        // $templateForm = $this->createForm(MailTemplateType::class);

        // デフォルトのページカウントを50件に設定
        $page_count = $this->session->get(
            'eccube.admin.destination.search.page_count',
            $this->eccubeConfig->get('eccube_default_page_count')
        );

        // 現在選択されている表示件数を取得
        $page_count_param = (int) $request->get('page_count');

        if ($page_count_param) {
            foreach ($pageMaxis as $pageMax) {
                if ($page_count_param == $pageMax->getName()) {
                    $page_count = $pageMax->getName();
                    $this->session->set('eccube.admin.destination.search.page_count', $page_count);
                    break;
                }
            }
        }

        // 検索が実行された場合
        if (isset($_POST['search'])) {
            $searchForm->handleRequest($request);

            if ($searchForm->isValid()) {
                /**
                 * セッションに検索条件を保存する.
                 * ページ番号は最初のページ番号に初期化する.
                 */
                $page_no = 1;
                $searchData = $searchForm->getData();

                // 検索条件, ページ番号をセッションに保持.
                $this->session->set('eccube.admin.destination.search', FormUtil::getViewData($searchForm));
                $this->session->set('eccube.admin.destination.search.page_no', $page_no);
            }
        } else {
            if (null !== $page_no || $request->get('resume')) {
                /*
                 * ページ送りの場合または、他画面から戻ってきた場合は, セッションから検索条件を復旧する.
                 */
                if ($page_no) {
                    // ページ送りで遷移した場合.
                    $this->session->set('eccube.admin.destination.search.page_no', (int) $page_no);
                } else {
                    // 他画面から遷移した場合.
                    $page_no = $this->session->get('eccube.admin.destination.search.page_no', 1);
                }
                $viewData = $this->session->get('eccube.admin.destination.search', []);
                $searchData = FormUtil::submitAndGetData($searchForm, $viewData);
            } else {
                /**
                 * 初期表示の場合.
                 */
                $page_no = 1;

                $viewData = FormUtil::getViewData($searchForm);
                $searchData = FormUtil::submitAndGetData($searchForm, $viewData);

                // セッション中の検索条件, ページ番号を初期化.
                $this->session->set('eccube.admin.destination.search', FormUtil::getViewData($searchForm));
                $this->session->set('eccube.admin.destination.search.page_no', $page_no);
            }
        }

        $qb = $this->customerRepository->getSearchData($searchData);

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
            'customers' => $customers,
            'templates' => $templates,
        ];
    }

    /** ajax通信用(送信ボタン押下時)
     * @Route("/%eccube_admin_route%/zooops_sendmail/send/preview", name="admin_zooops_sendmail_preview")
     */
    public function preview(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            throw new BadRequestHttpException();
        }

        // ブラウザで設定したテンプレートIDを取得
        $id = $request->get('id');

        // 設定したテンプレートIDの各要素を取得
        $template = $this->templateRepository->get($id);
        $template_name = $template->getTemplateName();
        $template_detail = $template->getTemplateDetail();
        // 改行コードをbrタグに置換
        $template_detail = str_replace("\r\n", "<br>", $template_detail);

        // json形式で返す
        return $this->json([
            'template_name' => $template_name,
            'template_detail' => $template_detail,
        ]);
    }

    /** ajax通信用(送信ボタン(モーダルダイアログ)押下時)
     * @Route("/%eccube_admin_route%/zooops_sendmail/send/csv/{template_id}", name="admin_zooops_sendmail_csv")
     */
    public function send(Request $request, $template_id = 0)
    {
        if (!$request->isXmlHttpRequest()) {
            throw new BadRequestHttpException();
        }

        $searchForm = $this->createForm(SearchDistinationType::class);

        $viewData = $this->session->get('eccube.admin.destination.search', []);
        $searchData = FormUtil::submitAndGetData($searchForm, $viewData);

        $this->sendMailProcess->csvExport($searchData, $template_id);

        // 戻り値なし
        return $this->json([]);
    }
}
