<?php

namespace Customize\Repository;

use Customize\Entity\Breeders;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Eccube\Util\StringUtil;
use Customize\Config\AnilineConf;

/**
 * @method Breeders|null find($id, $lockMode = null, $lockVersion = null)
 * @method Breeders|null findOneBy(array $criteria, array $orderBy = null)
 * @method Breeders[]    findAll()
 * @method Breeders[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BreedersRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Breeders::class);
    }

    /**
     * 新規Breederを作成する
     *
     * @return Breeder
     */
    public function newBreeder()
    {
        $Breeder = new \Customize\Entity\Breeders();
        $Breeder->setRegisterStatusId(AnilineConf::ANILINE_REGISTER_STATUS_PROVISIONAL);

        return $Breeder;
    }

    /**
     * ユニークなシークレットキーを返す.
     *
     * @return string
     */
    public function getUniqueSecretKey()
    {
        do {
            $key = StringUtil::random(32);
            $Breeders = $this->findOneBy(['secret_key' => $key]);
        } while ($Breeders);

        return $key;
    }

    /**
     * ユニークなパスワードリセットキーを返す
     *
     * @return string
     */
    public function getUniqueResetKey()
    {
        do {
            $key = StringUtil::random(32);
            $Breeders = $this->findOneBy(['reset_key' => $key]);
        } while ($Breeders);

        return $key;
    }

    /**
     * 仮会員をシークレットキーで検索する.
     *
     * @param $secretKey
     *
     * @return null|Breeders 見つからない場合はnullを返す.
     */
    public function getProvisionalConservationBySecretKey($secretKey)
    {
        return $this->findOneBy([
            'secret_key' => $secretKey,
            'Status' => CustomerStatus::PROVISIONAL,
        ]);
    }

    /**
     * 本会員をemailで検索する.
     *
     * @param $email
     *
     * @return null|Breeder 見つからない場合はnullを返す.
     */
    public function getRegularConservationByEmail($email)
    {
        return $this->findOneBy([
            'email' => $email,
            'register_status_id' => CustomerStatus::REGULAR,
        ]);
    }

    /**
     * 本会員をリセットキー、またはリセットキーとメールアドレスで検索する.
     *
     * @param $resetKey
     * @param $email
     *
     * @return null|Breeders 見つからない場合はnullを返す.
     */
    public function getRegularConservationByResetKey($resetKey, $email = null)
    {
        $qb = $this->createQueryBuilder('c')
            ->where('c.reset_key = :reset_key AND c.register_status_id = :status AND c.reset_expire >= :reset_expire')
            ->setParameter('reset_key', $resetKey)
            ->setParameter('status', CustomerStatus::REGULAR)
            ->setParameter('reset_expire', new \DateTime());

        if ($email) {
            $qb
                ->andWhere('c.email = :email')
                ->setParameter('email', $email);
        }

        return $qb->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * リセット用パスワードを生成する.
     *
     * @return string
     */
    public function getResetPassword()
    {
        return StringUtil::random(8);
    }

    /**
     * 仮会員, 本会員の会員を返す.
     * Eccube\Entity\BreedersのUniqueEntityバリデーションで使用しています.
     *
     * @param array $criteria
     *
     * @return Breeders[]
     */
    public function getNonWithdrawingConservations(array $criteria = [])
    {
        $criteria['register_status_id'] = [
            CustomerStatus::PROVISIONAL,
            CustomerStatus::REGULAR,
        ];

        return $this->findBy($criteria);
    }
}
