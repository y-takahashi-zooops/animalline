<?php

namespace Customize\Command;

use Customize\Entity\BatchSchedule;
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

    /**
     * @var SymfonyStyle
     */
    protected $io;

    public function __construct(
        EntityManagerInterface $entityManager,
        MovieConvertRepository $movieConvertRepository
    )
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->movieConvertRepository = $movieConvertRepository;
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
        $movies = $this->movieConvertRepository->findBy(['convert_status' => 0]);

        if (!$movies) {
            throw new NotFoundHttpException("Cannot find video in queue!");
        }

        foreach ($movies as $movie) {
            $cmd = 'ffmpeg -y -i ' . $movie->getSourcePath() . ' -r 30 -vb 340 ' . $movie->getDistPath() . ' -hide_banner -loglevel error 2>&1';
            $res = exec($cmd, $errors);
            if ($res) {
                $movie->setConvertStatus(2);
                $movie->setErrorReason($res);
                echo 'Caught exception: ', $res, "\n";
            } else {
                $movie->setConvertStatus(1);
                $movie->setErrorReason(null);
                echo 'File has been converted!', "\n";
            }
            $em->persist($movie);
            $em->flush();
        }
        return 0;
    }
}
