<?php

namespace Customize\Command;

use Customize\Config\AnilineConf;
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
        $yyyymm = $input->getArgument('yyyymm');
        $startDate = new \DateTimeImmutable($yyyymm . '01');
        $endDate = $startDate->modify('last day of this month')->setTime(23, 59, 59);

        $listUserNotCustomer = $this->customerRepository->getCustomer();
        foreach ($listUserNotCustomer as $user) {
            $listContract = $listOrder = [];
            $contractCommission = $ecIncentive = 0;

            $listContract = $user->getIsBreeder() ? $this->breederContactHeaderRepository
                ->getContractHeaderAMonth($startDate, $endDate, $user) :
                $this->conservationContactHeaderRepository
                ->getContractHeaderAMonth($startDate, $endDate, $user);
            foreach ($listContract as $item) {
                $contractCommission += $item->getPet()->getPrice() * AnilineConf::COMMISSION_RATE;
            }

            if ($customers = $this->customerRepository->findBy(['register_id' => $user->getId()])) {
                foreach ($customers as $customer) {
                    $listOrder = array_merge(
                        $listOrder,
                        $this->orderRepository->getOrderAMonthByCustomer($startDate, $endDate, $customer)
                    );
                }

                foreach ($listOrder as $item) {
                    foreach ($item->getOrderItems() as $orderItem) {
                        $ecIncentive += $orderItem->getPrice() * 0.01 * ($orderItem->getProductClass() ? $orderItem->getProductClass()->getIncentiveRatio() : 0);
                    }
                }
            }

            $monthlyInvoice = (new MonthlyInvoice)
                ->setCustomer($user)
                ->setSiteCategory($user->getSiteType())
                ->setYearmonth($yyyymm)
                ->setContractCount(count($listContract))
                ->setContractCommission($contractCommission)
                ->setEcCount(count($listOrder))
                ->setEcIncentive($ecIncentive)
                ->setTotalIncentive($contractCommission - $ecIncentive);

            $em = $this->entityManager;
            $em->persist($monthlyInvoice);
            $em->flush();
        }

        echo "Monthly invoices handled.\n";
    }
}
