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

namespace Eccube\Controller\Admin\Content;

use Doctrine\ORM\EntityManagerInterface;
use Customize\Config\AnilineConf;
use Eccube\Controller\AbstractController;
use Eccube\Entity\News;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Eccube\Form\Type\Admin\NewsType;
use Eccube\Repository\NewsRepository;
use Eccube\Util\CacheUtil;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Eccube\Common\EccubeConfig;

class NewsController extends AbstractController
{
    /**
     * @var NewsRepository
     */
    protected $newsRepository;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * NewsController constructor.
     *
     * @param NewsRepository $newsRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        NewsRepository $newsRepository,
        LoggerInterface $logger,
        EventDispatcherInterface $eventDispatcher,
        EccubeConfig $eccubeConfig
    )
    {
        $this->newsRepository = $newsRepository;
        $this->logger = $logger;
        $this->eventDispatcher = $eventDispatcher;
        $this->eccubeConfig = $eccubeConfig;
    }

    /**
     * 新着情報一覧を表示する。
     *
     * @Route("/%eccube_admin_route%/content/news", name="admin_content_news", methods={"GET"})
     * @Route("/%eccube_admin_route%/content/news/page/{page_no}", requirements={"page_no" = "\d+"}, name="admin_content_news_page", methods={"GET"})
     * 
     * @Template("@admin/Content/news.twig")
     *
     * @param Request $request
     * @param int|null  $page_no
     * @param PaginatorInterface $paginator
     *
     * @return array
     */
    public function index(Request $request, PaginatorInterface $paginator, $page_no = 1)
    {
        $qb = $this->newsRepository->getQueryBuilderAll();

        $event = new EventArgs(
            [
                'qb' => $qb,
            ],
            $request
        );
        $this->eventDispatcher->dispatch($event, EccubeEvents::ADMIN_CONTENT_NEWS_INDEX_INITIALIZE);

        $pagination = $paginator->paginate(
            $qb,
            $page_no,
            $this->eccubeConfig->get('eccube_default_page_count')
        );

        return [
            'pagination' => $pagination,
        ];
    }

    /**
     * 新着情報を登録・編集する。
     *
     * @Route("/%eccube_admin_route%/content/news/new", name="admin_content_news_new", methods={"GET", "POST"})
     * @Route("/%eccube_admin_route%/content/news/{id}/edit", requirements={"id" = "\d+"}, name="admin_content_news_edit", methods={"GET", "POST"})
     * 
     * @Template("@admin/Content/news_edit.twig")
     *
     * @param Request $request
     * @param null $id
     *
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function edit(Request $request, CacheUtil $cacheUtil, $id = null)
    {
        if ($id) {
            $News = $this->newsRepository->find($id);
            if (!$News) {
                throw new NotFoundHttpException();
            }
        } else {
            $News = new News();
            $News->setPublishDate(new \DateTime());
        }

        $builder = $this->formFactory
            ->createBuilder(NewsType::class, $News, ['img' => $request->get('img') ?? '']);

        $event = new EventArgs(
            [
                'builder' => $builder,
                'News' => $News,
            ],
            $request
        );
        $this->eventDispatcher->dispatch($event, EccubeEvents::ADMIN_CONTENT_NEWS_EDIT_INITIALIZE);

        $form = $builder->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->entityManager;
            if (!$News->getUrl()) {
                $News->setLinkMethod(false);
            }
            $this->newsRepository->save($News);
            $NewsId = $News->getId();
            $Img = $this->setImageSrc($request->get('img'), $NewsId);
            $event = new EventArgs(
                [
                    'form' => $form,
                    'News' => $News,
                ],
                $request
            );
            $this->eventDispatcher->dispatch($event, EccubeEvents::ADMIN_CONTENT_NEWS_EDIT_COMPLETE);

            $this->addSuccess('admin.common.save_complete', 'admin');

            // キャッシュの削除
            $cacheUtil->clearDoctrineCache();
            $News->setUrl($Img);
            $entityManager->persist($News);
            $entityManager->flush();
            return $this->redirectToRoute('admin_content_news_edit', ['id' => $News->getId()]);
        }

        return [
            'form' => $form->createView(),
            'News' => $News,
        ];
    }

    /**
     * 指定した新着情報を削除する。
     *
     * @Route("/%eccube_admin_route%/content/news/{id}/delete", requirements={"id" = "\d+"}, name="admin_content_news_delete", methods={"DELETE"})
     *
     * @param Request $request
     * @param News $News
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function delete(Request $request, News $News, CacheUtil $cacheUtil)
    {
        $this->isTokenValid();

        $this->logger->info('新着情報削除開始', [$News->getId()]);

        try {
            $this->newsRepository->delete($News);

            $event = new EventArgs(['News' => $News], $request);
            $this->eventDispatcher->dispatch($event, EccubeEvents::ADMIN_CONTENT_NEWS_DELETE_COMPLETE);

            $this->addSuccess('admin.common.delete_complete', 'admin');

            $this->logger->info('新着情報削除完了', [$News->getId()]);

            // キャッシュの削除
            $cacheUtil->clearDoctrineCache();
        } catch (\Exception $e) {
            $message = trans('admin.common.delete_error_foreign_key', ['%name%' => $News->getTitle()]);
            $this->addError($message, 'admin');

            log_error('新着情報削除エラー', [$News->getId(), $e]);
        }

        return $this->redirectToRoute('admin_content_news');
    }

    /**
     * Copy image and retrieve new url of the copy
     *
     * @param string $imageUrl
     * @param int $newsId
     * @return string
     */
    private function setImageSrc($imageUrl, $NewsId)
    {
        if (empty($imageUrl)) {
            return '';
        }

        $imageUrl = ltrim($imageUrl, '/');
        $resource = str_replace(
            AnilineConf::ANILINE_IMAGE_URL_BASE,
            '',
            $imageUrl
        );
        $arr = explode('/', ltrim($resource, '/'));
        if ($arr[0] === 'news') {
            return $resource;
        }

        $imageName = str_replace(
            AnilineConf::ANILINE_IMAGE_URL_BASE . '/tmp/',
            '',
            $imageUrl
        );
        $subUrl = AnilineConf::ANILINE_IMAGE_URL_BASE . '/news/' . $NewsId . '/';
        if (!file_exists($subUrl)) {
            mkdir($subUrl, 0777, 'R');
        }

        copy($imageUrl, $subUrl . $imageName);
        return '/news/' . $NewsId . '/' . $imageName;
    }

    /**
     * Upload image
     *
     * @Route("/news/upload", name="news_upload_crop_image", methods={"POST"}, options={"expose"=true})
     * @param Request $request
     * @return JsonResponse
     */
    public function upload(Request $request)
    {
        if (!file_exists(AnilineConf::ANILINE_IMAGE_URL_BASE . '/tmp/')) {
            mkdir(AnilineConf::ANILINE_IMAGE_URL_BASE . '/tmp/', 0777, 'R');
        }
        $folderPath = AnilineConf::ANILINE_IMAGE_URL_BASE . '/tmp/';
        $imageParts = explode(";base64,", $_POST['image']);
        $imageTypeAux = explode("image/", $imageParts[0]);
        $imageType = $imageTypeAux[1];
        $imageBase64 = base64_decode($imageParts[1]);
        $file = $folderPath . uniqid() . '.' . $imageType;
        file_put_contents($file, $imageBase64);
        return new JsonResponse($file);
    }
}
