<?php

namespace Customize\Command;

use Customize\Config\AnilineConf;
use Customize\Entity\BatchSchedule;
use Customize\Entity\BreederPetImage;
use Customize\Entity\ConservationPetImage;
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

class MovieConvert extends Command
{
    protected static $defaultName = 'eccube:customize:movie-convert';

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
     * MovieConvert constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param MovieConvertRepository $movieConvertRepository
     * @param ConservationPetsRepository $conservationPetsRepository
     * @param BreederPetsRepository $breederPetsRepository
     * @param BreederPetImageRepository $breederPetImageRepository
     * @param ConservationPetImageRepository $conservationPetImageRepository
     */
    public function __construct(
        EntityManagerInterface         $entityManager,
        MovieConvertRepository         $movieConvertRepository,
        ConservationPetsRepository     $conservationPetsRepository,
        BreederPetsRepository          $breederPetsRepository,
        BreederPetImageRepository      $breederPetImageRepository,
        ConservationPetImageRepository $conservationPetImageRepository
    )
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->movieConvertRepository = $movieConvertRepository;
        $this->conservationPetsRepository = $conservationPetsRepository;
        $this->breederPetsRepository = $breederPetsRepository;
        $this->breederPetImageRepository = $breederPetImageRepository;
        $this->conservationPetImageRepository = $conservationPetImageRepository;
    }

    protected function configure()
    {
        $this->setDescription('Convert movie format');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $em = $this->entityManager;
        $movies = $this->movieConvertRepository->findBy(['convert_status' => AnilineConf::MOVIE_IN_QUEUE]);

        if (!$movies) {
            throw new NotFoundHttpException("Cannot find video in queue!");
        }

        foreach ($movies as $movie) {
            $cmd = 'mkdir -p "' . AnilineConf::ANILINE_IMAGE_URL_BASE . '/movie/tmp/' . $movie->getSiteCategory() .
                '/' . $movie->getPetId() . '" && ffmpeg -y -i ' . $movie->getSourcePath() . ' -r 30 -vb 340k ' .
                $movie->getDistPath() . ' -hide_banner -loglevel error 2>&1';
            $res = exec($cmd, $errors);
            if ($res) {
                $movie->setConvertStatus(AnilineConf::MOVIE_CONVERT_FAIL);
                $movie->setErrorReason($res);
                echo 'Caught exception: ', $res, "\n";
            } else {
                if ($movie->getSiteCategory() == AnilineConf::MOVIE_CONSERVATION_PET) {
                    $conservation_pet = $this->conservationPetsRepository->find($movie->getPetId());
                    if (!$conservation_pet) {
                        echo "Cannot find pet!\n";
                        continue;
                    }
                    $issetPetImage = $this->conservationPetImageRepository->findBy(['image_uri' => $movie->getDistPath()]);
                    if ($issetPetImage) {
                        echo "URI pet image is existed!\n";
                        continue;
                    }
                    $conservation = new ConservationPetImage();
                    $conservation->setConservationPet($conservation_pet)
                        ->setImageType(AnilineConf::PET_PHOTO_TYPE_VIDEO)
                        ->setImageUri($movie->getDistPath())
                        ->setSortOrder(0);
                    $em->persist($conservation);
                } else {
                    $breeder_pet = $this->breederPetsRepository->find($movie->getPetId());
                    if (!$breeder_pet) {
                        echo "Cannot find pet!\n";
                        continue;
                    }
                    $issetPetImage = $this->breederPetImageRepository->findBy(['image_uri' => $movie->getDistPath()]);
                    if ($issetPetImage) {
                        echo "URI pet image is existed!\n";
                        continue;
                    }
                    $breeder = new BreederPetImage();
                    $breeder->setBreederPet($breeder_pet)
                        ->setImageType(AnilineConf::PET_PHOTO_TYPE_VIDEO)
                        ->setImageUri($movie->getDistPath())
                        ->setSortOrder(0);
                    $em->persist($breeder);
                }
                $movie->setConvertStatus(AnilineConf::MOVIE_CONVERT_SUCCESS);
                $movie->setErrorReason(null);
                echo "File has been converted!\n";
            }
            $em->persist($movie);
            $em->flush();
        }
        return 0;
    }
}
