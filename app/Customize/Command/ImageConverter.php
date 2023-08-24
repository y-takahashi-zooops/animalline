<?php

namespace Customize\Command;

use Customize\Config\AnilineConf;
use Customize\Entity\BatchSchedule;
use Customize\Entity\BreederPetImage;
use Customize\Entity\ConservationPetImage;
use Customize\Repository\BreedersRepository;
use Customize\Repository\BreederPetsRepository;
use Customize\Repository\ConservationPetsRepository;
use Customize\Repository\ConservationPetImageRepository;
use Customize\Repository\BreederPetImageRepository;
use Customize\Repository\MovieConvertRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ImageConverter extends Command
{
    protected static $defaultName = 'eccube:customize:image-convert';

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var MovieConvertRepository
     */
    protected $movieConvertRepository;

    /**
     * @var ConservationPetsRepository
     */
    protected $conservationPetsRepository;

    /**
     * @var BreederPetsRepository
     */
    protected $breederPetsRepository;

    /**
     * @var BreederPetImageRepository
     */
    protected $breederPetImageRepository;

    /**
     * @var ConservationPetImageRepository
     */
    protected $conservationPetImageRepository;

    /**
     * @var SymfonyStyle
     */
    protected $io;

    /**
     * @var BreedersRepository
     */
    protected $breedersRepository;

    /**
     * MovieConvert constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param MovieConvertRepository $movieConvertRepository
     * @param ConservationPetsRepository $conservationPetsRepository
     * @param BreederPetsRepository $breederPetsRepository
     * @param BreederPetImageRepository $breederPetImageRepository
     * @param ConservationPetImageRepository $conservationPetImageRepository
     * @param BreedersRepository $breedersRepository
     */
    public function __construct(
        EntityManagerInterface         $entityManager,
        MovieConvertRepository         $movieConvertRepository,
        ConservationPetsRepository     $conservationPetsRepository,
        BreederPetsRepository          $breederPetsRepository,
        BreederPetImageRepository      $breederPetImageRepository,
        ConservationPetImageRepository $conservationPetImageRepository,
        BreedersRepository $breedersRepository
    )
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->movieConvertRepository = $movieConvertRepository;
        $this->conservationPetsRepository = $conservationPetsRepository;
        $this->breederPetsRepository = $breederPetsRepository;
        $this->breederPetImageRepository = $breederPetImageRepository;
        $this->conservationPetImageRepository = $conservationPetImageRepository;
        $this->breedersRepository = $breedersRepository;
    }

    protected function configure()
    {
        $this->setDescription('Convert movie format');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    private function convertBreederThumbnail(){
        $base_path = "html/upload/images/";
        $breeder_image_path = "breeder/";

        $fp = fopen("var/log/convert/BreederThumb".date("Ymdhis").".log","w");

        //ベースディレクトリの作成
        if(!file_exists($base_path)){
            mkdir($base_path, 0777);
        }

        //ブリーダー情報の画像格納フォルダ作成
        if(!file_exists($base_path.$breeder_image_path)){
            mkdir($base_path.$breeder_image_path, 0777);
        }

        $em = $this->entityManager;

        //ブリーダー
        $sql = "select id,thumbnail_path FROM alm_breeders WHERE thumbnail_path not like :param";

        $statement = $em->getConnection()->prepare($sql);
        $statement->bindValue("param", "%.webp");
        $statement->execute();

        $results = $statement->fetchAll();

        foreach($results as $result){
            $breeder = $this->breedersRepository->find($result["id"]);
            $original_img = $breeder->getThumbnailPath();
            $convert_img = $base_path.$breeder_image_path.uniqid().".webp";

            if($this->convertWebp($original_img, $convert_img)){
                $breeder->setThumbnailPath("/".$convert_img);
                $em->persist($breeder);

                echo("Convert breeder thubnail : ".$original_img." >> ".$convert_img."\n");
                fputs($fp, $original_img.",".$convert_img."\n");
            }
        }
        $em->flush();

        fclose($fp);
    }

    private function convertBreederPetThumbnail(){
        $base_path = "html/upload/save_image";

        $em = $this->entityManager;

        $fp = fopen("var/log/convert/BreederPetThumb".date("Ymdhis").".log","w");

        //ペット情報
        $sql = "select id,thumbnail_path FROM alm_breeder_pets WHERE thumbnail_path not like :param";

        $statement = $em->getConnection()->prepare($sql);
        $statement->bindValue("param", "%.webp");
        $statement->execute();

        $results = $statement->fetchAll();

        foreach($results as $result){
            $breeder_pet = $this->breederPetsRepository->find($result["id"]);
            $original_img = $breeder_pet->getThumbnailPath();

            //ファイルではない場合はスキップ
            if(is_dir($base_path.$original_img)){
                continue;
            }

            //コンバート先
            $convert_img = dirname($original_img)."/".uniqid().".webp";

            if($this->convertWebp($base_path.$original_img, $base_path.$convert_img)){
                $breeder_pet->setThumbnailPath($convert_img);
                $em->persist($breeder_pet);

                fputs($fp, $original_img.",".$convert_img."\n");
                echo("Convert breeder Pet thubnail : ".$original_img." >> ".$convert_img."\n");
            }
        }
        $em->flush();

        fclose($fp);
    }


    private function convertBreederPetImage(){
        $base_path = "html/upload/save_image";

        $em = $this->entityManager;

        $fp = fopen("var/log/convert/BreederPetImage".date("Ymdhis").".log","w");

        //ペット情報
        $sql = "select id,image_uri FROM alm_breeder_pet_image WHERE image_uri <> '' and image_uri not like :param";

        $statement = $em->getConnection()->prepare($sql);
        $statement->bindValue("param", "%.webp");
        $statement->execute();

        $results = $statement->fetchAll();

        foreach($results as $result){
            $breeder_pet_image = $this->breederPetImageRepository->find($result["id"]);
            $original_img = $breeder_pet_image->getImageUri();

            //ファイルではない場合はスキップ
            if(is_dir($base_path.$original_img)){
                continue;
            }

            //コンバート先
            $convert_img = dirname($original_img)."/".uniqid().".webp";

            if($this->convertWebp($base_path.$original_img, $base_path.$convert_img)){
                $breeder_pet_image->setImageUri($convert_img);
                $em->persist($breeder_pet_image);

                fputs($fp, $original_img.",".$convert_img."\n");
                echo("Convert breeder Pet thubnail : ".$original_img." >> ".$convert_img."\n");
            }
        }
        $em->flush();

        fclose($fp);
    }
    
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->convertBreederThumbnail();
        $this->convertBreederPetThumbnail();
        $this->convertBreederPetImage();
        
        return 0;
    }

    private function convertWebp($before, $after) {
        var_dump($before);

        if(substr($before,0,1) == "/"){
            $before = substr($before,1);
        }
        if(substr($after,0,1) == "/"){
            $after = substr($after,1);
        }

        if(!file_exists($before)){
            return false;
        }

        $image_type = exif_imagetype($before);

        switch($image_type) {
            case IMAGETYPE_JPEG:
                $image = imagecreatefromjpeg($before);
                break;
                
            case IMAGETYPE_PNG:
                $image = imagecreatefrompng($before);
                break;
            case IMAGETYPE_GIF:
                $image = imagecreatefromgif($before);
                break;

            default:
                return false;
        }

        //幅が1280以下の場合は1280に
        list($width, $height) = getimagesize($before);

        if($width > 1280){
            // 縦横のリサイズ後のピクセル数を求める
            $ratio = 1280 / $width;
            $newWidth = floor($width * $ratio);
            $newHeight = floor($height * $ratio);

            $newImage = imagecreatetruecolor($newWidth, $newHeight);
            if(imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height)){
                $image = $newImage;
            }

            echo("Resize : ".$width." > ".$newWidth."\n");
        }
        echo("Save : ".$after."\n");
        if(!imagewebp($image,$after)){
            echo("保存できませんでした。\n");
            return false;
        }

        return true;
    }
}
