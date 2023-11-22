<?php

namespace Customize\Entity;

use Doctrine\ORM\Mapping as ORM;

if (!class_exists('\Customize\Entity\ScheduledMail')) {
    /**
     * Shop
     *
     * @ORM\Table(name="d_scheduled_mail")
     * @ORM\InheritanceType("SINGLE_TABLE")
     * @ORM\DiscriminatorColumn(name="discriminator_type", type="string", length=255)
     * @ORM\HasLifecycleCallbacks()
     * @ORM\Entity(repositoryClass="Customize\Repository\ScheduledMailRepository")
     */
    class ScheduledMail extends \Eccube\Entity\AbstractEntity
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
         * @var \DateTime
         *
         * @ORM\Column(name="send_date", type="date", nullable=false)
         */
        private $send_date;

        /**
         * @var \DateTime
         *
         * @ORM\Column(name="mail_type", type="integer", nullable=false)
         */
        private $mail_type;

        /**
         * @var \Eccube\Entity\Order
         *
         * @ORM\ManyToOne(targetEntity="Eccube\Entity\Order", inversedBy="SubscriptionContracts")
         * @ORM\JoinColumns({
         *   @ORM\JoinColumn(name="order_id", referencedColumnName="id")
         * })
         */
        private $Order;

        /**
         * @var \Eccube\Entity\Customer
         *
         * @ORM\ManyToOne(targetEntity="Eccube\Entity\Customer", inversedBy="SubscriptionContracts")
         * @ORM\JoinColumns({
         *   @ORM\JoinColumn(name="customer_id", referencedColumnName="id")
         * })
         */
        private $Customer;

        /**
         * @var \DateTime
         *
         * @ORM\Column(name="execute_time", type="datetimetz", nullable=true)
         */
        private $execute_time;

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
         * Get id
         *
         * @return integer
         */
        public function getId()
        {
            return $this->id;
        }

        // mail_type ***************************************
        public function setMailType($mail_type)
        {
            $this->mail_type = $mail_type;

            return $this;
        }
        public function getMailType()
        {
            return $this->mail_type;
        }
        // mail_type ***************************************
        
        // execute_time ***************************************
        public function setExecuteTime($execute_time)
        {
            $this->execute_time = $execute_time;

            return $this;
        }
        public function getExecuteTime()
        {
            return $this->execute_time;
        }
        // execute_time ***************************************

        // customer_id ***************************************
        public function setCustomer($Customer)
        {
            $this->Customer = $Customer;

            return $this;
        }
        public function getCustomer()
        {
            return $this->Customer;
        }
        // customer_id ***************************************

        // order_id ***************************************
        public function setOrder($Order)
        {
            $this->Order = $Order;

            return $this;
        }
        public function getOrder()
        {
            return $this->Order;
        }
        // order_id ***************************************


        // send_date ***************************************
        public function setSendDate($send_date)
        {
            $this->send_date = $send_date;

            return $this;
        }
        public function getSendDate()
        {
            return $this->send_date;
        }
        // send_date ***************************************

        /**
         * set createDate
         *
         * @param \DateTime $createDate
         *
         * @return SubscriptionContract
         */
        public function setCreateDate($createDate)
        {
            $this->create_date = $createDate;

            return $this;
        }

        /**
         * get createDate
         *
         * @return \DateTime
         */
        public function getCreateDate()
        {
            return $this->create_date;
        }

        /**
         * set updateDate
         *
         * @param \DateTime $updateDate
         *
         * @return SubscriptionContract
         */
        public function setUpdateDate($updateDate)
        {
            $this->update_date = $updateDate;

            return $this;
        }

        /**
         * get updateDate
         *
         * @return \DateTime
         */
        public function getUpdateDate()
        {
            return $this->update_date;
        }
    }
}
