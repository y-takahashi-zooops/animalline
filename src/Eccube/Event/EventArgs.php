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

namespace Eccube\Event;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Class EventArgs
 */
class EventArgs extends Event
{
    /**
     * @var Request|null
     */
    private $request;

    /**
     * @var Response|null
     */
    private $response;

    /**
     * EventArgs constructor.
     *
     * @param array $arguments
     * @param Request $request
     * @param Response $response
     */
    public function __construct(array $arguments = [], ?Request $request = null, ?Response $response = null)
    {
        parent::__construct(null, $arguments);
        $this->request = $request;
        $this->response = $response;
    }

    public function setRequest(Request $request): self
    {
        $this->request = $request;
        return $this;
    }

    public function getRequest(): ?Request
    {
        return $this->request;
    }

    /**
     * @param Response|null $response
     */
    public function setResponse(Response $response): self
    {
        $this->response = $response;
        return $this;
    }

    /**
     * @return Response|null
     */
    public function getResponse(): ?Response
    {
        return $this->response;
    }

    /**
     * @return bool
     */
    public function hasResponse(): bool
    {
        return $this->response !== null;
    }
}