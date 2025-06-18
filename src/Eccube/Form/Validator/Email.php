<?php

/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) EC-CUBE CO.,LTD. All Rights Reserved.
 *
 * http://www.ec-cube.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eccube\Form\Validator;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
class Email extends \Symfony\Component\Validator\Constraints\Email
{
    /**
     * RFC準拠の厳密な検証を行うかどうか（trueで行う）
     *
     * @var bool
     */
    public $strict = false;
}
