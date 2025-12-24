<?php

/*
 * Copyright(c) 2022 GMO Payment Gateway, Inc. All rights reserved.
 * http://www.gmo-pg.com/
 */

namespace Plugin\GmoPaymentGateway4\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * GmoFraudDetection
 *
 * @ORM\Table(name="plg_gmo_payment_gateway_fraud_detection")
 * @ORM\Entity(repositoryClass="Plugin\GmoPaymentGateway4\Repository\GmoFraudDetectionRepository")
 */
class GmoFraudDetection
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", options={"unsigned":true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="ip_address", type="string", length=15)
     */
    private $ip_address;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="occur_time", type="datetimetz")
     */
    private $occur_time;

    /**
     * @var int
     *
     * @ORM\Column(name="error_count", type="integer", options={"unsigned":true,"default":0})
     */
    private $error_count;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="create_date", type="datetimetz")
     */
    private $create_date;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="update_date", type="datetimetz")
     */
    private $update_date;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getIpAddress()
    {
        return $this->ip_address;
    }

    /**
     * @return \DateTime
     */
    public function getOccurTime()
    {
        return $this->occur_time;
    }

    /**
     * @return int
     */
    public function getErrorCount()
    {
        return $this->error_count;
    }

    /**
     * @return \DateTime
     */
    public function getCreateDate()
    {
        return $this->create_date;
    }

    /**
     * @return \DateTime
     */
    public function getUpdateDate()
    {
        return $this->update_date;
    }

    /**
     * @param string $ip_address
     *
     * @return $this;
     */
    public function setIpAddress($ip_address)
    {
        $this->ip_address = $ip_address;

        return $this;
    }

    /**
     * @param \DateTime $occur_time
     *
     * @return $this;
     */
    public function setOccurTime($occur_time)
    {
        $this->occur_time = $occur_time;

        return $this;
    }

    /**
     * @param integer $error_count
     *
     * @return $this;
     */
    public function setErrorCount($error_count)
    {
        $this->error_count = $error_count;

        return $this;
    }

    /**
     * @param \DateTime $create_date
     *
     * @return $this;
     */
    public function setCreateDate($create_date)
    {
        $this->create_date = $create_date;

        return $this;
    }

    /**
     * @param \DateTime $update_date
     *
     * @return $this;
     */
    public function setUpdateDate($update_date)
    {
        $this->update_date = $update_date;

        return $this;
    }
}
