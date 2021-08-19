<?php

namespace Customize\Command;

use Customize\Config\AnilineConf;
use Customize\Entity\BatchSchedule;
use Customize\Entity\BreederPetImage;
use Customize\Entity\ConservationPetImage;
use Customize\Repository\BreederPetsRepository;
use Customize\Repository\ConservationPetsRepository;
use Customize\Repository\MovieConvertRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 */
class MovieConvert extends Command
{
    protected static $defaultName = 'eccube:customize:movie-convert';

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    protected $movieConvertRepository;

    protected $conservationPetsRepository;

    protected $breederPetsRepository;

    /**
     * @var SymfonyStyle
     */
    protected $io;

    public function __construct(
        EntityManagerInterface     $entityManager,
        MovieConvertRepository     $movieConvertRepository,
        ConservationPetsRepository $conservationPetsRepository,
        BreederPetsRepository      $breederPetsRepository

    )
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->movieConvertRepository = $movieConvertRepository;
        $this->conservationPetsRepository = $conservationPetsRepository;
        $this->breederPetsRepository = $breederPetsRepository;
    }

    protected function configure()
    {
        $this->setDescription('');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->entityManager;
        $movies = $this->movieConvertRepository->findBy(['convert_status' => AnilineConf::MOVIE_IN_QUEUE]);

        if (!$movies) {
            throw new NotFoundHttpException("Cannot find video in queue!");
        }

        foreach ($movies as $movie) {
            $cmd = 'ffmpeg -y -i ' . $movie->getSourcePath() . ' -r 30 -vb 340k ' . $movie->getDistPath() . ' -hide_banner -loglevel error 2>&1';
            $res = exec($cmd, $errors);
            if ($res) {
                $movie->setConvertStatus(AnilineConf::MOVIE_CONVERT_FAIL);
                $movie->setErrorReason($res);
                echo 'Caught exception: ', $res, "\n";
            } else {
                $movie->setConvertStatus(AnilineConf::MOVIE_CONVERT_SUCCESS);
                $movie->setErrorReason(null);

                if ($movie->getSiteCategory() == AnilineConf::MOVIE_CONSERVATION_PET) {
                    $conservation = new ConservationPetImage();
                    $conservation_pet = $this->conservationPetsRepository->find($movie->getPetId());
                    if (!$conservation_pet) {
                        throw new NotFoundHttpException("Cannot find pet!");
                    }
                    $conservation->setConservationPetId($conservation_pet)
                        ->setImageType(AnilineConf::PET_PHOTO_TYPE_VIDEO)
                        ->setImageUri($movie->getDistPath())
                        ->setSortOrder(0);
                    $em->persist($conservation);
                } else {
                    $breeder = new BreederPetImage();
                    $breeder_pet = $this->breederPetsRepository->find($movie->getPetId());
                    if (!$breeder_pet) {
                        throw new NotFoundHttpException("Cannot find pet!");
                    }
                    $breeder->setImageUri($movie->getDistPath())
                        ->setImageType(AnilineConf::PET_PHOTO_TYPE_VIDEO)
                        ->setBreederPetId($breeder_pet)
                        ->setSortOrder(0);
                    $em->persist($breeder);
                }
                echo 'File has been converted!', "\n";
            }
            $em->persist($movie);
            $em->flush();
        }
        return 0;
    }
}
