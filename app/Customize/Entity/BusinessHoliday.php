<?php

namespace Customize\Entity;

use Doctrine\ORM\Mapping as ORM;

if (!class_exists('\Customize\Entity\BusinessHoliday')) {
    /**
     * Shop
     *
     * @ORM\Table(name="d_business_holiday")
     * @ORM\InheritanceType("SINGLE_TABLE")
     * @ORM\DiscriminatorColumn(name="discriminator_type", type="string", length=255)
     * @ORM\HasLifecycleCallbacks()
     * @ORM\Entity(repositoryClass="Customize\Repository\BusinessHolidayRepository")
     */
    class BusinessHoliday extends \Eccube\Entity\AbstractEntity
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
         * @ORM\Column(name="holiday_date", type="datetimetz", nullable=true)
         */
        private $holiday_date;

        /**
         * Get id
         *
         * @return integer
         */
        public function getId()
        {
            return $this->id;
        }

        /**
         * Set holiday_date
         *
         * @param \DateTime $holiday_date
         * 
         * @return BusinessHoliday
         */
        public function setHolidayDate(?\DateTime $holiday_date = null)
        {
            $this->holiday_date = $holiday_date;

            return $this;
        }

        /**
         * Get holiday_date.
         *
         * @return \DateTime
         */
        public function getHolidayDate()
        {
            return $this->holiday_date;
        }
    }
}
