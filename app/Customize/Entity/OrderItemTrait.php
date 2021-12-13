<?php

/*
 * Copyright(c) 2018 GMO Payment Gateway, Inc. All rights reserved.
 * http://www.gmo-pg.com/
 */

namespace Customize\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Annotation\EntityExtension;

/**
 * @EntityExtension("Eccube\Entity\OrderItemTrait")
 */
trait OrderItemTrait
{
    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    public $is_repeat;

    /**
     * @ORM\Column(type="smallint", options={"unsigned":true}, nullable=true)
     */
    public $repeat_span;

    /**
     * @ORM\Column(type="boolean", options={"unsigned":true}, nullable=true)
     */
    public $span_unit;

    /**
     * Set is_repeat.
     *
     * @param boolean $is_repeat
     *
     * @return CartItem
     */
    public function setIsRepeat($is_repeat)
    {
        $this->is_repeat = $is_repeat;

        return $this;
    }

    /**
     * Get is_repeat.
     *
     * @return boolean
     */
    public function getIsRepeat()
    {
        return $this->is_repeat;
    }

    /**
     * Set repeat_span.
     *
     * @param smallint $repeat_span
     *
     * @return CartItem
     */
    public function SetRepeatSpan($repeat_span)
    {
        $this->repeat_span = $repeat_span;

        return $this;
    }

    /**
     * Get repeat_span.
     *
     * @return smallint
     */
    public function getRepeatSpan()
    {
        return $this->repeat_span;
    }

    /**
     * Set span_unit.
     *
     * @param smallint $span_unit
     *
     * @return CartItem
     */
    public function SetSpanUnit($span_unit)
    {
        $this->span_unit = $span_unit;

        return $this;
    }

    /**
     * Get span_unit.
     *
     * @return smallint
     */
    public function getSpanUnit()
    {
        return $this->span_unit;
    }
}
