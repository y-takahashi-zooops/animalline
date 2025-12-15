<?php

namespace Plugin\ZooopsSendmail\Entity;

use Doctrine\ORM\Mapping as ORM;

if (!class_exists('\Plugin\ZooopsSendmail\Entity\MailTemplate')) {
    /**
     * Shop
     *
     * @ORM\Table(name="plg_zooops_sendmail_template")
     * @ORM\InheritanceType("SINGLE_TABLE")
     * @ORM\DiscriminatorColumn(name="discriminator_type", type="string", length=255)
     * @ORM\HasLifecycleCallbacks()
     * @ORM\Entity(repositoryClass="Plugin\ZooopsSendmail\Repository\MailTemplateRepository")
     */
    class MailTemplate extends \Eccube\Entity\AbstractEntity
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
         * @ORM\Column(name="template_name", type="string", length=255)
         */
        private $template_name;

        /**
         * @var string
         *
         * @ORM\Column(name="template_title", type="text")
         */
        private $template_title;

        /**
         * @var string
         *
         * @ORM\Column(name="template_detail", type="text")
         */
        private $template_detail;

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
         * Set id
         *
         * @param integer $id
         *
         * @return MailTemplate
         */
        public function setId($id)
        {
            $this->id = $id;

            return $this;
        }

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
         * Set template_name
         *
         * @param string $template_name
         *
         * @return MailTemplate
         */
        public function setTemplateName($template_name)
        {
            $this->template_name = $template_name;

            return $this;
        }

        /**
         * Get template_name
         *
         * @return string
         */
        public function getTemplateName()
        {
            return $this->template_name;
        }

        /**
         * Set template_title
         *
         * @param string $template_name
         *
         * @return MailTemplate
         */
        public function setTemplateTitle($template_title)
        {
            $this->template_title = $template_title;

            return $this;
        }

        /**
         * Get template_title
         *
         * @return string
         */
        public function getTemplateTitle()
        {
            return $this->template_title;
        }

        /**
         * Set template_detail
         *
         * @param string $template_detail
         *
         * @return MailTemplate
         */
        public function setTemplateDetail($template_detail)
        {
            $this->template_detail = $template_detail;

            return $this;
        }

        /**
         * Get template_detail
         *
         * @return string
         */
        public function getTemplateDetail()
        {
            return $this->template_detail;
        }

		/**
         * Set createDate
         *
         * @param \DateTime $createDate
         *
         * @return MailTemplate
         */
        public function setCreateDate($createDate)
        {
            $this->create_date = $createDate;

            return $this;
        }

        /**
         * Get createDate
         *
         * @return \DateTime
         */
        public function getCreateDate()
        {
            return $this->create_date;
        }

        /**
         * Set updateDate
         *
         * @param \DateTime $updateDate
         *
         * @return MailTemplate
         */
        public function setUpdateDate($updateDate)
        {
            $this->update_date = $updateDate;

            return $this;
        }

        /**
         * Get updateDate
         *
         * @return \DateTime
         */
        public function getUpdateDate()
        {
            return $this->update_date;
        }
    }
}
