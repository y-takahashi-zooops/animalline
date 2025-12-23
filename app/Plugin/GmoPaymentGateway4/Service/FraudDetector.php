<?php
/*
 * Copyright(c) 2022 GMO Payment Gateway, Inc. All rights reserved.
 * http://www.gmo-pg.com/
 */

namespace Plugin\GmoPaymentGateway4\Service;

use Doctrine\ORM\EntityManagerInterface;
use Plugin\GmoPaymentGateway4\Entity\GmoFraudDetection;
use Plugin\GmoPaymentGateway4\Repository\GmoFraudDetectionRepository;
use Plugin\GmoPaymentGateway4\Repository\GmoPaymentMethodRepository;
use Plugin\GmoPaymentGateway4\Util\PaymentUtil;

/**
 * 決済プラグイン用 不正検知に関する情報を処理するクラス
 */
class FraudDetector
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var Plugin\GmoPaymentGateway4\Repository\GmoFraudDetectionRepository
     */
    protected $gmoFraudDetectionRepository;

    /**
     * @var Plugin\GmoPaymentGateway4\Repository\GmoPaymentMethodRepository
     */
    protected $gmoPaymentMethodRepository;

    /**
     * 不正検知機能を利用するか否か
     * @var int
     */
    protected $use_limit;

    /**
     * ロック検出時間(分)
     * @var int
     */
    protected $limit_min;

    /**
     * エラー上限回数
     * @var int
     */
    protected $limit_count;

    /**
     * ロック時間(分)
     * @var int
     */
    protected $lock_min;

    /**
     * コンストラクタ
     *
     * @param EntityManagerInterface $entityManager
     * @param GmoFraudDetectionRepository $gmoFraudDetectionRepository
     * @param GmoPaymentMethodRepository $gmoPaymentMethodRepository
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        GmoFraudDetectionRepository $gmoFraudDetectionRepository,
        GmoPaymentMethodRepository $gmoPaymentMethodRepository
    ) {
        $this->entityManager = $entityManager;
        $this->gmoFraudDetectionRepository = $gmoFraudDetectionRepository;
        $this->gmoPaymentMethodRepository = $gmoPaymentMethodRepository;
        $this->use_limit = 0;
        $this->limit_min = 0;
        $this->limit_count = 0;
        $this->lock_min = 0;
    }

    /**
     * 決済クラス名で初期化する
     *
     * @param string|null $className GMO決済クラス名
     */
    public function initPaymentMethodClass($className = null)
    {
        PaymentUtil::logInfo(__METHOD__ . ' start.');

        if (!is_null($className)) {
            $gmoPaymentMethodConfig = $this->gmoPaymentMethodRepository
                ->getGmoPaymentMethodConfig($className);

            if (!empty($gmoPaymentMethodConfig['use_limit'])) {
                $this->use_limit = $gmoPaymentMethodConfig['use_limit'];
            }
            if (!empty($gmoPaymentMethodConfig['limit_min'])) {
                $this->limit_min = $gmoPaymentMethodConfig['limit_min'];
            }
            if (!empty($gmoPaymentMethodConfig['limit_count'])) {
                $this->limit_count = $gmoPaymentMethodConfig['limit_count'];
            }
            if (!empty($gmoPaymentMethodConfig['lock_min'])) {
                $this->lock_min = $gmoPaymentMethodConfig['lock_min'];
            }
        }

        PaymentUtil::logInfo(__METHOD__ . ' end. [' .
                             'ul:' . $this->use_limit .
                             ' lm:' . $this->limit_min .
                             ' lc:' . $this->limit_count .
                             ' lo:' . $this->lock_min .
                             ']');
    }

    /**
     * リモートアドレスを取得する
     *
     * @return string リモートIPアドレス
     */
    public function getRemoteAddr()
    {
        PaymentUtil::logInfo(__METHOD__ . ' start.');

        $ipAddr = $_SERVER['REMOTE_ADDR'];

        if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) &&
            !empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ipAddrs = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ipAddr = $ipAddrs[0];
        }

        PaymentUtil::logInfo(__METHOD__ . ' end. [' . $ipAddr . ']');

        return $ipAddr;
    }

    /**
     * ロック中かどうかをチェック
     *
     * @param string $ipAddr 省略時はリモートIPアドレスを自動取得
     * @return boolean true:ロック, false:非ロック
     */
    public function isLock($ipAddr = '')
    {
        PaymentUtil::logInfo(__METHOD__ . ' start.');

        if (!$this->use_limit) {
            // 不正検知機能を利用しない場合は常に非ロックを返す
            PaymentUtil::logInfo('Not use fraud detector.');
            return false;
        }

        if (empty($ipAddr)) {
            $ipAddr = $this->getRemoteAddr();
        }
        PaymentUtil::logInfo('IP: ' . $ipAddr);

        $GmoFraudDetection = $this->gmoFraudDetectionRepository
            ->findOneBy(['ip_address' => $ipAddr]);

        if (is_null($GmoFraudDetection)) {
            PaymentUtil::logInfo(__METHOD__ . ' end. [Not Lock]');
            return false;
        }

        $lock_time = clone $GmoFraudDetection->getOccurTime();
        $lock_time->add(new \DateInterval('PT' . $this->lock_min . 'M'));

        $r = false;
        $error_count = $GmoFraudDetection->getErrorCount();
        if ($error_count >= $this->limit_count &&
            $lock_time->format('YmdHis') >= date('YmdHis')) {
            $r = true;
        }

        PaymentUtil::logInfo(__METHOD__ . ' end. [' .
                             ($r ? 'Lock' : 'Not lock') . ']');

        return $r;
    }

    /**
     * ロックを解除する
     *
     * @param string $ipAddr 省略時はリモートIPアドレスを自動取得
     */
    public function unLock($ipAddr = '')
    {
        PaymentUtil::logInfo(__METHOD__ . ' start.');

        if (empty($ipAddr)) {
            $ipAddr = $this->getRemoteAddr();
        }
        PaymentUtil::logInfo('IP: ' . $ipAddr);

        $GmoFraudDetection = $this->gmoFraudDetectionRepository
            ->findOneBy(['ip_address' => $ipAddr]);

        if (is_null($GmoFraudDetection)) {
            return;
        }

        $this->entityManager->remove($GmoFraudDetection);
        $this->entityManager->flush();

        PaymentUtil::logInfo(__METHOD__ . ' end.');
    }

    /**
     * エラーをカウントしロック状態を返す
     *
     * @param string $ipAddr 省略時はリモートIPアドレスを自動取得
     * @return boolean true:ロック, false:非ロック
     */
    public function errorOccur($ipAddr = '')
    {
        PaymentUtil::logInfo(__METHOD__ . ' start.');

        if (!$this->use_limit) {
            // 不正検知機能を利用しない場合は常に非ロックを返す
            PaymentUtil::logInfo('Not use fraud detector.');
            return false;
        }

        if (empty($ipAddr)) {
            $ipAddr = $this->getRemoteAddr();
        }
        PaymentUtil::logInfo('IP: ' . $ipAddr);

        $now = new \DateTime();

        $GmoFraudDetection = $this->gmoFraudDetectionRepository
            ->findOneBy(['ip_address' => $ipAddr]);

        if (is_null($GmoFraudDetection)) {
            $GmoFraudDetection = new GmoFraudDetection();
            $GmoFraudDetection
                ->setIpAddress($ipAddr)
                ->setOccurTime($now)
                ->setErrorCount(0);
        }

        $lock_time = clone $GmoFraudDetection->getOccurTime();
        $lock_time->add(new \DateInterval('PT' . $this->lock_min . 'M'));

        $limit_time = clone $GmoFraudDetection->getOccurTime();
        $limit_time->add(new \DateInterval('PT' . $this->limit_min . 'M'));

        $error_count = $GmoFraudDetection->getErrorCount();
        if ($error_count >= $this->limit_count) {
            // エラー回数が制限数を超えている場合

            if ($lock_time->format('YmdHis') < $now->format('YmdHis')) {
                // ロック時間を経過
                $GmoFraudDetection
                    ->setOccurTime($now)
                    ->setErrorCount(1);
            }
        } else {
            // エラー回数が制限数を超えていない場合

            if ($limit_time->format('YmdHis') >= $now->format('YmdHis')) {
                // 検出時間内
                $error_count = $GmoFraudDetection->getErrorCount();
                $GmoFraudDetection->setErrorCount(++$error_count);
            } else {
                // 検出時間外
                $GmoFraudDetection
                    ->setOccurTime($now)
                    ->setErrorCount(1);
            }
        }

        // 保存
        $this->entityManager->persist($GmoFraudDetection);
        $this->entityManager->flush();

        $r = $this->isLock($ipAddr);

        PaymentUtil::logInfo(__METHOD__ . ' end.');

        return $r;
    }

    /**
     * 不正検知ロックの解除日時を返す
     *
     * @param string $ipAddr IPアドレス
     * @return DateTime
     */
    public function getReleaseTime($ipAddr)
    {
        PaymentUtil::logInfo(__METHOD__ . ' start. [' . $ipAddr . ']');

        $GmoFraudDetection = $this->gmoFraudDetectionRepository
            ->findOneBy(['ip_address' => $ipAddr]);
        if (is_null($GmoFraudDetection)) {
            return new \DateTime();
        }

        $lock_time = clone $GmoFraudDetection->getOccurTime();
        $lock_time->add(new \DateInterval('PT' . $this->lock_min . 'M'));

        PaymentUtil::logInfo(__METHOD__ . ' end. [' .
                             $lock_time->format('Y/m/d H:i:s') . ']');

        return $lock_time;
    }

    /**
     * 不正検知ロックの状態を返す
     *
     * @param string $ipAddr IPアドレス
     * @return string
     */
    public function getStatus($ipAddr)
    {
        PaymentUtil::logInfo(__METHOD__ . ' start. [' . $ipAddr . ']');

        $status = '';

        if ($this->isLock($ipAddr)) {
            $status =
                trans('gmo_payment_gateway.admin.fraud_detection.status');
        }

        PaymentUtil::logInfo(__METHOD__ . ' end. [' . $status . ']');

        return $status;
    }
}
?>
