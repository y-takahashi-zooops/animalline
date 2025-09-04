<?php

namespace Customize\Controller\Adoption;

use Carbon\Carbon;
use Customize\Service\AdoptionQueryService;
use DateTime;
use Customize\Config\AnilineConf;
use Customize\Entity\ConservationPetImage;
use Customize\Entity\ConservationPets;
use Customize\Repository\DnaCheckStatusHeaderRepository;
use Customize\Repository\DnaCheckStatusRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Customize\Form\Type\Adoption\ConservationPetsType;
use Customize\Repository\ConservationPetsRepository;
use Customize\Repository\PetsFavoriteRepository;
use Customize\Repository\ConservationContactHeaderRepository;
use Customize\Repository\ConservationContactsRepository;
use Customize\Repository\ConservationsRepository;
use Customize\Repository\ConservationPetImageRepository;
use Eccube\Controller\AbstractController;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception as HttpException;
use Eccube\Repository\CustomerRepository;
use Customize\Form\Type\Breeder\BreederPetMovieType;
use Symfony\Component\Form\FormFactoryInterface;
use Doctrine\ORM\EntityManagerInterface;

class AdoptionPetController extends AbstractController
{
    /**
     * @var ConservationPetsRepository
     */
    protected $conservationPetsRepository;

    /**
     * @var DnaCheckStatusRepository
     */
    protected $dnaCheckStatusRepository;

    /**
     * @var PetsFavoriteRepository
     */
    protected $petsFavoriteRepository;

    /**
     * @var ConservationsRepository
     */
    protected $conservationsRepository;

    /**
     * @var ConservationPetImageRepository
     */
    protected $conservationPetImageRepository;

    /**
     * @var DnaCheckStatusHeaderRepository
     */
    protected $dnaCheckStatusHeaderRepository;

    /**
     * @var ConservationContactHeaderRepository
     */
    protected $conservationContactHeaderRepository;

    /**
     * @var ConservationContactsRepository
     */
    protected $conservationContactsRepository;

    /**
     * @var AdoptionQueryService
     */
    protected $adoptionQueryService;

    /**
     * @var CustomerRepository
     */
    protected $customerRepository;

    /**
     * @var FormFactoryInterface
     */
    protected $formFactory;

    /**
     * ConservationController constructor.
     *
     * @param ConservationPetsRepository $conservationPetsRepository
     * @param DnaCheckStatusRepository $dnaCheckStatusRepository
     * @param ConservationsRepository $conservationsRepository
     * @param ConservationPetImageRepository $conservationPetImageRepository
     * @param PetsFavoriteRepository $petsFavoriteRepository
     * @param DnaCheckStatusHeaderRepository $dnaCheckStatusHeaderRepository
     * @param AdoptionQueryService $adoptionQueryService
     * @param ConservationContactsRepository $conservationContactsRepository
     * @param CustomerRepository $customerRepository
     */
    public function __construct(
        ConservationPetsRepository          $conservationPetsRepository,
        DnaCheckStatusRepository            $dnaCheckStatusRepository,
        ConservationsRepository             $conservationsRepository,
        ConservationPetImageRepository      $conservationPetImageRepository,
        PetsFavoriteRepository              $petsFavoriteRepository,
        DnaCheckStatusHeaderRepository      $dnaCheckStatusHeaderRepository,
        ConservationContactHeaderRepository $conservationContactHeaderRepository,
        AdoptionQueryService                $adoptionQueryService,
        ConservationContactsRepository      $conservationContactsRepository,
        CustomerRepository      $customerRepository,
        FormFactoryInterface $formFactory,
        EntityManagerInterface $entityManager
    )
    {
        $this->conservationPetsRepository = $conservationPetsRepository;
        $this->dnaCheckStatusRepository = $dnaCheckStatusRepository;
        $this->conservationsRepository = $conservationsRepository;
        $this->conservationPetImageRepository = $conservationPetImageRepository;
        $this->petsFavoriteRepository = $petsFavoriteRepository;
        $this->dnaCheckStatusHeaderRepository = $dnaCheckStatusHeaderRepository;
        $this->conservationContactHeaderRepository = $conservationContactHeaderRepository;
        $this->adoptionQueryService = $adoptionQueryService;
        $this->conservationContactsRepository = $conservationContactsRepository;
        $this->customerRepository = $customerRepository;
        $this->formFactory = $formFactory;
        $this->entityManager = $entityManager;
    }

    /**
     * 取扱ペット一覧TOP
     *
     * @Route("/adoption/member/pet_list", name="adoption_pet_list")
     * @Template("animalline/adoption/member/pet_list.twig")
     */
    public function adoption_pet_list(PaginatorInterface $paginator, Request $request)
    {
        $user = $this->getUser();

        $petlist_view = $request->get("petlist_view");
        if(!$petlist_view){$petlist_view = 1;}

        $arrayPets = [];
        $pets = $this->adoptionQueryService->getListPet($this->getUser(),$petlist_view);

        foreach ($pets as $key => $pet) {
            $pet['check'] = false;
            if ($pet['cch_id']) {
                //問い合わせが2件以上ある場合はidを-1に設定する。
                $Headers = $this->conservationContactHeaderRepository->findBy(["Conservation" => $user]);
                if(count($Headers) > 1){
                    $pet['cch_id'] = -1;
                }
                else {
                    $msgHeader = $this->conservationContactHeaderRepository->find($pet['cch_id']);
                    $pet['message'] = $this->conservationContactsRepository->findOneBy(['ConservationContactHeader' => $msgHeader, 'message_from' => AnilineConf::MESSAGE_FROM_USER], ['create_date' => 'DESC']);
                    if ($msgHeader->getConservationNewMsg() == AnilineConf::NEW_MESSAGE) {
                        $pet['check'] = true;
                    }
                    if ($pet['message']) {
                        if ($pet['message']->getIsReading() == AnilineConf::RESPONSE_UNREPLIED) {
                            $pet['check'] = true;
                        }
                    }
                }
            }
            $arrayPets[$pet['cp_id']] = $pet;
        }

        $arrayPets = $paginator->paginate(
            array_reverse($arrayPets),
            $request->query->getInt('page', 1),
            AnilineConf::ANILINE_NUMBER_ITEM_PER_PAGE
        );

        return $this->render(
            'animalline/adoption/member/pet_list.twig',
            [
                'conservation' => $this->getUser(),
                'pets' => $arrayPets,
                'petlist_view' => $petlist_view,
            ]
        );
    }

    /**
     * ペット詳細
     *
     * @Route("/adoption/pet/detail/{id}", name="adoption_pet_detail", requirements={"id" = "\d+"})
     * @Template("animalline/adoption/pet/detail.twig")
     */
    public function petDetail(Request $request)
    {
        $isLoggedIn = (bool)$this->getUser();
        $id = $request->get('id');
        $isFavorite = false;
        $conservationPet = $this->conservationPetsRepository->find($id);
        $favorite = $this->petsFavoriteRepository->findOneBy(['Customer' => $this->getUser(), 'pet_id' => $id]);
        if ($favorite) {
            $isFavorite = true;
        }
        if (!$conservationPet) {
            throw new HttpException\NotFoundHttpException();
        }
        $petKind = $conservationPet->getPetKind();
        $images = $this->conservationPetImageRepository->findBy(
            [
                'ConservationPet' => $conservationPet,
                'image_type' => AnilineConf::PET_PHOTO_TYPE_IMAGE
            ]
        );
        $video = $this->conservationPetImageRepository->findOneBy(
            [
                'ConservationPet' => $conservationPet,
                'image_type' => AnilineConf::PET_PHOTO_TYPE_VIDEO
            ]
        );

        $html_title = "ペット詳細 - ".$conservationPet->getBreedsType()->getbreedsName();
        
        //$maintitle = "犬・猫ブリーダー直販のアニマルライン";
        $breadcrumb = array(
            array('url' => $this->generateUrl('adoption_top'),'title' =>"保護団体TOP"),
            array('url' => "#",'title' => $conservationPet->getBreedsType()->getbreedsName())
        );

        $entityManager = $this->entityManager;
        $conservationPet->setViewCount(intval($conservationPet->getViewCount() + 1));
        $entityManager->persist($conservationPet);
        $entityManager->flush();

        return $this->render(
            'animalline/adoption/pet/detail.twig',
            [
                'title' => $html_title,
                'conservationPet' => $conservationPet,
                'images' => $images,
                'video' => $video,
                'isFavorite' => $isFavorite,
                'isLoggedIn' => $isLoggedIn,
                'petKind' => $petKind,
                'maintitle' => $html_title,
                'breadcrumb' => $breadcrumb,
            ]
        );
    }

    /**
     * 新規ペット追加
     *
     * @Route("/adoption/member/pets/new/{conservation_id}", name="adoption_pets_new", methods={"GET","POST"})
     */
    public function adoption_pets_new(Request $request,$conservation_id = ""): Response
    {
        if($conservation_id != ""){
            //conservation_id指定がある場合はログインユーザーチェックを行い、許可ユーザーであれば指定のブリーダーをシミュレート
            $user = $this->getUser();
            if($user->getId() == 91 || $user->getId() == 236){
                $user = $this->customerRepository->find($conservation_id);

                if(!$user){
                    throw new NotFoundHttpException();
                }
            }
            else{
                throw new NotFoundHttpException();
            }
        }
        else{
            //conservation_id指定がない場合はログイン中ユーザーとして処理
            $user = $this->getUser();
        }
        
        $is_conservation = $user->getIsConservation();
        $conservation = $this->conservationsRepository->find($user);
        if ($is_conservation == 0) {
            return $this->render('animalline/adopution/member/examination_guidance.twig', [
                'conservation' => $conservation
            ]);
        }

        $image0 = $request->get('img0') ?? '';
        $image1 = $request->get('img1') ?? '';
        $image2 = $request->get('img2') ?? '';
        $image3 = $request->get('img3') ?? '';
        $image4 = $request->get('img4') ?? '';

        if ($request->get('clone_id') && $conservationPetClone = $this->conservationPetsRepository->find($request->get('clone_id'))) {
            $conservationPet = clone $conservationPetClone;
            $conservationPet->setReleaseDate(null);
        } else $conservationPet = new ConservationPets();

        $form = $this->createForm(ConservationPetsType::class, $conservationPet, [
            'customer' => $user
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->entityManager;
            $conservationPet->setConservation($conservation);
            $conservationPet->setDnaCheckResult(0);
            $conservationPet->setIsActive(intval($conservation->getIsActive()));
            $bd = new DateTime('now');
            $conservationPet->setPetBirthday($bd);
            $conservationPet->setFutureWait(0);
            $conservationPet->setViewCount(0);
            $entityManager->persist($conservationPet);
            $entityManager->flush();
            $petId = $conservationPet->getId();

            $img0 = $this->setImageSrc($request->get('img0'), $petId);
            $img1 = $this->setImageSrc($request->get('img1'), $petId);
            $img2 = $this->setImageSrc($request->get('img2'), $petId);
            $img3 = $this->setImageSrc($request->get('img3'), $petId);
            $img4 = $this->setImageSrc($request->get('img4'), $petId);

            $petImage0 = (new ConservationPetImage())
                ->setImageType(AnilineConf::PET_PHOTO_TYPE_IMAGE)->setImageUri($img0)->setSortOrder(1)
                ->setConservationPet($conservationPet);
            $petImage1 = (new ConservationPetImage())
                ->setImageType(AnilineConf::PET_PHOTO_TYPE_IMAGE)->setImageUri($img1)->setSortOrder(2)
                ->setConservationPet($conservationPet);
            $petImage2 = (new ConservationPetImage())
                ->setImageType(AnilineConf::PET_PHOTO_TYPE_IMAGE)->setImageUri($img2)->setSortOrder(3)
                ->setConservationPet($conservationPet);
            $petImage3 = (new ConservationPetImage())
                ->setImageType(AnilineConf::PET_PHOTO_TYPE_IMAGE)->setImageUri($img3)->setSortOrder(4)
                ->setConservationPet($conservationPet);
            $petImage4 = (new ConservationPetImage())
                ->setImageType(AnilineConf::PET_PHOTO_TYPE_IMAGE)->setImageUri($img4)->setSortOrder(5)
                ->setConservationPet($conservationPet);
            $conservationPet->addConservationPetImage($petImage0);
            $conservationPet->addConservationPetImage($petImage1);
            $conservationPet->addConservationPetImage($petImage2);
            $conservationPet->addConservationPetImage($petImage3);
            $conservationPet->addConservationPetImage($petImage4);
            $conservationPet->setThumbnailPath($img0);

            $entityManager->persist($petImage0);
            $entityManager->persist($petImage1);
            $entityManager->persist($petImage2);
            $entityManager->persist($petImage3);
            $entityManager->persist($petImage4);

            $entityManager->flush();

            if($conservation_id != ""){
                return $this->redirectToRoute('close_window');
            }
            else{
                return $this->redirectToRoute('adoption_pet_list');
            }
        }

        return $this->render('animalline/adoption/member/pets/new.twig', [
            'form' => $form->createView(),
            'image0' => $image0,
            'image1' => $image1,
            'image2' => $image2,
            'image3' => $image3,
            'image4' => $image4,
            'conservation' => $conservation,
            'isCheckPetContract' => false
        ]);
    }

    /**
     *
     * 新規ペット追加完了メッセージ
     *
     * @Route("/adoption/member/pets/new_complete", name="adoption_newpet_complete", methods={"GET","POST"})
     * @Template("animalline/adoption/member/pets/notification.twig")
     */
    public function adoption_pets_new_complete()
    {
        return [];
    }

    /**
     * ペットの状態を変更する
     *
     * @Route("/adoption/member/pets/{id}/change_status", name="adoption_pets_edit_change_status", methods={"GET"})
     */
    public function adoption_pets_change_status(Request $request, ConservationPets $conservationPet)
    {
        $curStatus = $conservationPet->getIsActive();
        if ($curStatus === AnilineConf::IS_ACTIVE_PRIVATE) {
            $conservationPet->setIsActive(AnilineConf::IS_ACTIVE_PUBLIC);
            $conservationPet->setReleaseDate(Carbon::now());
        } elseif ($curStatus === AnilineConf::IS_ACTIVE_PUBLIC) {
            $conservationPet->setIsActive(AnilineConf::IS_ACTIVE_PRIVATE);
            $conservationPet->setReleaseDate(null);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($conservationPet);
        $em->flush();

        return $this->redirectToRoute('adoption_pets_edit', ['id' => $conservationPet->getId()]);
    }

    /**
     * ペットの状態を削除する
     *
     * @Route("/adoption/member/pets/{id}/delete", name="adoption_pets_delete", methods={"GET"})
     */
    public function adoption_pets_delete(ConservationPets $conservationPet)
    {
        //非公開にセットする
        $conservationPet->setIsActive(0);

        $em = $this->getDoctrine()->getManager();
        $em->persist($conservationPet);
        $em->flush();

        return $this->redirectToRoute('adoption_pet_list');
    }

    /**
     * ペットの状態をトライアル中する
     *
     * @Route("/adoption/member/pets/{id}/status1", name="adoption_pets_status-1", methods={"GET"})
     */
    public function adoption_pets_status1(ConservationPets $conservationPet)
    {
        //非公開にセットする
        $conservationPet->setIsActive(1);

        $em = $this->getDoctrine()->getManager();
        $em->persist($conservationPet);
        $em->flush();

        return $this->redirectToRoute('adoption_pet_list');
    }

    /**
     * ペットの状態をトライアル中する
     *
     * @Route("/adoption/member/pets/{id}/status2", name="adoption_pets_status-2", methods={"GET"})
     */
    public function adoption_pets_status2(ConservationPets $conservationPet)
    {
        //非公開にセットする
        $conservationPet->setIsActive(2);

        $em = $this->getDoctrine()->getManager();
        $em->persist($conservationPet);
        $em->flush();

        return $this->redirectToRoute('adoption_pet_list');
    }

    /**
     * ペットの状態をトライアル中する
     *
     * @Route("/adoption/member/pets/{id}/status3", name="adoption_pets_status-3", methods={"GET"})
     */
    public function adoption_pets_status3(ConservationPets $conservationPet)
    {
        //非公開にセットする
        $conservationPet->setIsActive(3);

        $em = $this->getDoctrine()->getManager();
        $em->persist($conservationPet);
        $em->flush();

        return $this->redirectToRoute('adoption_pet_list');
    }

    /**
     * ペット情報編集
     *
     * @Route("/adoption/member/pets/edit/{id}/{conservation_id}", name="adoption_pets_edit", methods={"GET","POST"})
     * @param Request $request
     * @param ConservationPets $conservationPet
     * @return Response
     */
    public function adoption_pets_edit(Request $request, ConservationPets $conservationPet, string $conservation_id = ""): Response
    {
        if($conservation_id != ""){
            //breeder_id指定がある場合はログインユーザーチェックを行い、許可ユーザーであれば指定のブリーダーをシミュレート
            $user = $this->getUser();
            if($user->getId() == 91 || $user->getId() == 236){
                $user = $this->customerRepository->find($conservation_id);

                if(!$user){
                    throw new NotFoundHttpException();
                }
            }
            else{
                throw new NotFoundHttpException();
            }
        }
        else{
            //breeder_id指定がない場合はログイン中ユーザーとして処理
            $user = $this->getUser();
        }

        $isCheckPetContract = !is_null($this->conservationContactHeaderRepository->findOneBy(['Pet' => $conservationPet, 'contract_status' => AnilineConf::CONTRACT_STATUS_CONTRACT]));

        $image0 = $request->get('img0') ?? '';
        $image1 = $request->get('img1') ?? '';
        $image2 = $request->get('img2') ?? '';
        $image3 = $request->get('img3') ?? '';
        $image4 = $request->get('img4') ?? '';

        $form = $this->createForm(ConservationPetsType::class, $conservationPet, [
            'customer' => $user
        ]);
        $conservationPetImages = $this->conservationPetImageRepository->findBy(
            ['ConservationPet' => $conservationPet, 'image_type' => AnilineConf::PET_PHOTO_TYPE_IMAGE],
            ['sort_order' => 'ASC']
        );

        $request->request->set('thumbnail_path', $conservationPet->getThumbnailPath() ? '/' . AnilineConf::ANILINE_IMAGE_URL_BASE . $conservationPet->getThumbnailPath() : '');

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $request->request->set('thumbnail_path', $image0);
            if ($form->isValid()) {
                $petId = $conservationPet->getId();
                $img0 = $this->setImageSrc($request->get('img0'), $petId);
                $img1 = $this->setImageSrc($request->get('img1'), $petId);
                $img2 = $this->setImageSrc($request->get('img2'), $petId);
                $img3 = $this->setImageSrc($request->get('img3'), $petId);
                $img4 = $this->setImageSrc($request->get('img4'), $petId);
                $entityManager = $this->entityManager;
                $conservationPet->setThumbnailPath($img0);

                $entityManager->persist($conservationPet);
                foreach ($conservationPetImages as $key => $image) {
                    $image->setImageUri(${'img' . $key});
                    $entityManager->persist($image);
                }
                $entityManager->flush();

                if($conservation_id != ""){
                    //管理画面からの場合はWindowを閉じる
                    return $this->redirectToRoute('close_window');
                }
                else{
                    return $this->redirectToRoute('adoption_pet_list');
                }
            }
        }

        $petImages = [];
        foreach ($conservationPetImages as $key => $image) {
            if ($form->isSubmitted()) {
                $petImages[$key] = [
                    'image_uri' => $request->get('img' . $key),
                    'sort_order' => $image->getSortOrder()
                ];
            } else {
                $petImages[$key] = [
                    'image_uri' => $image->getImageUri() ? '/' . AnilineConf::ANILINE_IMAGE_URL_BASE . $image->getImageUri() : '',
                    'sort_order' => $image->getSortOrder()
                ];
            }
        }

        return $this->render('animalline/adoption/member/pets/edit.twig', [
            'adoption_pet' => $conservationPet,
            'pet_mages' => $petImages,
            'form' => $form->createView(),
            'thumbnailPath' => $request->get('thumbnail_path'),
            'image0' => $image0,
            'image1' => $image1,
            'image2' => $image2,
            'image3' => $image3,
            'image4' => $image4,
            'conservation' => $conservationPet->getConservation(),
            'isCheckPetContract' => $isCheckPetContract
        ]);
    }

    /**
     * Copy image and retrieve new url of the copy
     *
     * @param string $imageUrl
     * @param int $petId
     * @return string
     */
    private function setImageSrc($imageUrl, $petId)
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
        if ($arr[0] === 'adoption') {
            return $resource;
        }

        $imageName = str_replace(
            AnilineConf::ANILINE_IMAGE_URL_BASE . '/tmp/',
            '',
            $imageUrl
        );
        $subUrl = AnilineConf::ANILINE_IMAGE_URL_BASE . '/adoption/' . $petId . '/';
        if (!file_exists($subUrl)) {
            mkdir($subUrl, 0777, 'R');
        }

        copy($imageUrl, $subUrl . $imageName);
        return '/adoption/' . $petId . '/' . $imageName;
    }

    /**
     *
     * ペット登録一覧
     *
     * @Route("/adoption/member/pet_regist_list", name="adoption_pet_regist_list")
     * @Template("animalline/adoption/member/pets/regist_list.twig")
     */
    public function pet_regist_list(Request $request, PaginatorInterface $paginator)
    {
        $codes = [];
        $dnaCheckStatusHeaders = $this->dnaCheckStatusHeaderRepository->findBy(['register_id' => $this->getUser()->getId()]);
        $DnaCheckStatus = $this->dnaCheckStatusRepository->createQueryBuilder('dcs')
            ->where('dcs.DnaHeader IN(:arr)')
            ->andWhere('dcs.site_type = :siteType')
            ->andWhere('dcs.check_status = :checkStatus')
            ->setParameter('siteType', AnilineConf::ANILINE_SITE_TYPE_ADOPTION)
            ->setParameter('checkStatus', AnilineConf::ANILINE_DNA_CHECK_STATUS_SHIPPING)
            ->setParameter('arr', $dnaCheckStatusHeaders)
            ->addOrderBy('dcs.update_date', 'DESC')
            ->getQuery()->getResult();
        foreach ($DnaCheckStatus as $dnaCheckStatus) {
            $codes[] = '2' . str_pad($dnaCheckStatus->getId(), 5, '0', STR_PAD_LEFT);
        }
        $barCodes = $paginator->paginate(
            $codes,
            $request->query->getInt('page', 1),
            AnilineConf::ANILINE_NUMBER_ITEM_PER_PAGE
        );
        return compact('barCodes');
    }

    /**
     * 動画投稿
     *
     * @Route("/adoption/member/movie_upload/{pet_id}", name="adoption_movie_upload", requirements={"pet_id" = "\d+"})
     * @Template("animalline/adoption/member/movie_upload.twig")
     */
    public function movie_upload(Request $request,$pet_id)
    {
        $entityManager = $this->entityManager;

        $pet = $this->conservationPetsRepository->find($pet_id);
        if (!$pet) {
            throw new HttpException\NotFoundHttpException();
        }

        $builder = $this->formFactory->createBuilder(BreederPetMovieType::class);

        $form = $builder->getForm();
        $form->handleRequest($request);

        $thumbnail_path = $request->get('thumbnail_path') ?? '';

        if ($form->isSubmitted() && $form->isValid()) {
            
            //受信ファイル処理
            $brochureFile = $form->get('movie_file')->getData();
                        
            if($brochureFile){
                $originalFilename = pathinfo($brochureFile->getClientOriginalName(), PATHINFO_FILENAME);
                $newFilename = 'pmovie-'.uniqid().'.'.$brochureFile->guessExtension();

                $brochureFile->move(
                    "html/upload/movie/",
                    $newFilename
                );

                $pet->setMovieFile($newFilename);
                $entityManager->persist($pet);
                $entityManager->flush();
            }
        }

        return ['pet' => $pet,'form' => $form->createView()];
    }
}
