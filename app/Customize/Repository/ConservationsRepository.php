<?php

namespace Customize\Repository;

use Customize\Entity\Conservations;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Eccube\Util\StringUtil;
use Customize\Config\AnilineConf;

/**
 * @method Conservations|null find($id, $lockMode = null, $lockVersion = null)
 * @method Conservations|null findOneBy(array $criteria, array $orderBy = null)
 * @method Conservations[]    findAll()
 * @method Conservations[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConservationsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Conservations::class);
    }

    /**
     * 新規Conservationを作成する
     *
     * @return Conservation
     */
    public function newConservation()
    {
        $Conservation = new \Customize\Entity\Conservations();
        $Conservation->setRegisterStatusId(AnilineConf::ANILINE_REGISTER_STATUS_PROVISIONAL);

        return $Conservation;
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
            $Conservation = $this->findOneBy(['secret_key' => $key]);
        } while ($Conservation);

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
            $Conservation = $this->findOneBy(['reset_key' => $key]);
        } while ($Conservation);

        return $key;
    }

    /**
     * 仮会員をシークレットキーで検索する.
     *
     * @param $secretKey
     *
     * @return null|Conservation 見つからない場合はnullを返す.
     */
    public function getProvisionalConservationBySecretKey($secretKey)
    {
        return $this->findOneBy([
            'secret_key' => $secretKey,
            'register_status_id' => CustomerStatus::PROVISIONAL,
        ]);
    }

    /**
     * 本会員をemailで検索する.
     *
     * @param $email
     *
     * @return null|Conservation 見つからない場合はnullを返す.
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
     * @return null|Customer 見つからない場合はnullを返す.
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
     * Eccube\Entity\CustomerのUniqueEntityバリデーションで使用しています.
     *
     * @param array $criteria
     *
     * @return Customer[]
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
