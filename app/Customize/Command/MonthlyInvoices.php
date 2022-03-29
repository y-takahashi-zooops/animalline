<?php

namespace Customize\Command;

use Customize\Entity\MonthlyInvoice;
use Customize\Repository\BreederContactHeaderRepository;
use Customize\Repository\ConservationContactHeaderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Eccube\Repository\CustomerRepository;
use Eccube\Repository\OrderRepository;
use Eccube\Repository\ProductClassRepository;
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
     * @var BreederContactHeaderRepository
     */
    protected $breederContactHeaderRepository;

    /**
     * @var ConservationContactHeaderRepository
     */
    protected $conservationContactHeaderRepository;

    /**
     * @var OrderRepository
     */
    protected $orderRepository;

    /**
     * @var ProductClassRepository
     */
    protected $productClassRepository;

    /**
     * Monthly invoices constructor.
     * 
     * @param EntityManagerInterface $entityManager
     * @param CustomerRepository $customerRepository
     * @param BreederContactHeaderRepository $breederContactHeaderRepository
     * @param ConservationContactHeaderRepository $conservationContactHeaderRepository
     * @param OrderRepository $orderRepository
     * @param ProductClassRepository $productClassRepository
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        CustomerRepository $customerRepository,
        BreederContactHeaderRepository $breederContactHeaderRepository,
        ConservationContactHeaderRepository $conservationContactHeaderRepository,
        OrderRepository $orderRepository,
        ProductClassRepository $productClassRepository
    ) {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->customerRepository = $customerRepository;
        $this->breederContactHeaderRepository = $breederContactHeaderRepository;
        $this->conservationContactHeaderRepository = $conservationContactHeaderRepository;
        $this->orderRepository = $orderRepository;
        $this->productClassRepository = $productClassRepository;
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
        $listOrder = [];
        $contractCommission = 0;
        $ecIncentive = 0;
        $yyyymm = $input->getArgument('yyyymm');
        $startDate = new \DateTimeImmutable($yyyymm . '01');
        $endDate = $startDate->modify('last day of this month')->setTime(23, 59, 59);
        $listUserNotCustomer = $this->customerRepository->getCustomer();
        foreach ($listUserNotCustomer as $user) {
            $listContract = [];
            if ($this->customerRepository->findBy(['register_id' => $user->getId()])) {
                $listCustomer[$user->getId()] = $this->customerRepository->findBy(['register_id' => $user->getId()]);
                foreach ($listCustomer[$user->getId()] as $customer) {
                    $contractCommission = 0;
                    $ecIncentive = 0;
                    if ($user->getIsBreeder() == 1) {
                        $listContract = $this->breederContactHeaderRepository->getContractHeaderAMonth($startDate, $endDate, $user);
                    }
                   if ($user->getIsConservation() == 1) {
                       $listContract = $this->conservationContactHeaderRepository->getContractHeaderAMonth($startDate, $endDate, $user);
                   }
                    $listOrder = $this->orderRepository->getOrderAMonthByCustomer($startDate, $endDate, $customer);
                    foreach ($listContract as $item) {
                        $pricePet = $item->getPet()->getPrice();
                        $contractCommission = $contractCommission + $pricePet * 0.15;
                    }
                    foreach ($listOrder as $item) {
                        foreach ($item->getOrderItems() as $orderItem) {
                            $productClass = $this->productClassRepository->find($orderItem->getProductClass);
                            $ecIncentive = $ecIncentive + ($orderItem->getPrice * ($productClass->getIncentiveRatio/100));
                        }
                    }
                }
            }

            $em = $this->entityManager;
            $monthlyInvoice = new MonthlyInvoice();
            $monthlyInvoice->setCustomerId($user)
                ->setSiteCategory($user->getIsBreeder() == 1 ? 1 : 2)
                ->setYearmonth($yyyymm)
                ->setContractCount(count($listContract))
                ->setContractCommission($contractCommission)
                ->setEcCount(count($listOrder))
                ->setEcIncentive($ecIncentive)
                ->setTotalIncentive($contractCommission - $ecIncentive);
            $em->persist($monthlyInvoice);
            $em->flush();
        }
        
        echo "Monthly invoices handled.\n";
    }
}
