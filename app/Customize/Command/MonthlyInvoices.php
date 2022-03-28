<?php

namespace Customize\Command;

use Doctrine\ORM\EntityManagerInterface;
use Eccube\Repository\CustomerRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MonthlyInvoices extends Command
{
    protected static $defaultName = 'eccube:customize:monthlyinvoice';

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var SymfonyStyle
     */
    protected $io;

    /**
     * @var CustomerRepository
     */
    protected $customerRepository;

    /**
     * Monthly invoices constructor.
     * 
     * @param EntityManagerInterface $entityManager
     * @param CustomerRepository $customerRepository
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        CustomerRepository $customerRepository
    ) {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->customerRepository = $customerRepository;
    }

    protected function configure()
    {
        $this->addArgument('yyyymm', InputArgument::REQUIRED, 'Year month.')
            ->setDescription('Monthly invoices.');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $yyyymm = $input->getArgument('yyyymm');

        // TODO: add logic

        echo "Monthly invoices handled.\n";
    }
}
