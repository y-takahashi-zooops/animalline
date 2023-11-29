<?php

/*
 * Copyright(c) 2018 GMO Payment Gateway, Inc. All rights reserved.
 * http://www.gmo-pg.com/
 */

namespace Customize\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Annotation\EntityExtension;
use Customize\Entity\Pets;

/**
 * @EntityExtension("Eccube\Entity\Order")
 */
trait OrderTrait
{
    /**
     * @ORM\Column(name="nosend_wms", type="smallint", nullable=true, options={"default":0})
     */
    public $nosend_wms = 0;
    
    public function getNosendWms()
    {
        return $this->nosend_wms;
    }

    public function setNosendWms($nosend_wms)
    {
        $this->nosend_wms = $nosend_wms;
    }
}
