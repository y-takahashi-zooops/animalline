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

namespace Eccube\ServiceProvider;

use Eccube\Application;
use Eccube\Common\EccubeConfig;
use Pimple\Container;

class EccubeServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(Container $pimple)
    {
        $app = $pimple;

        $app['orm.em'] = function () use ($app) {
            return $app->getParentContainer()->get('doctrine')->getManager();
        };

        $app['config'] = function () use ($app) {
            if ($app->getParentContainer()->has(EccubeConfig::class)) {
                return $app->getParentContainer()->get(EccubeConfig::class);
            }

            return [];
        };

        $app['monolog.logger'] = function () use ($app) {
            return $app->getParentContainer()->get('logger');
        };
        $app['monolog'] = function () use ($app) {
            return $app['monolog.logger'];
        };
        $app['eccube.logger'] = function () use ($app) {
            return $app->getParentContainer()->get('eccube.logger');
        };

        $app['session'] = function () use ($app) {
            return $app->getParentContainer()->get('session');
        };

        $app['form.factory'] = function () use ($app) {
            return $app->getParentContainer()->get('form.factory');
        };

        $app['security'] = function () use ($app) {
            return $app->getParentContainer()->get('security.token_storage');
        };

        $app['user'] = function () use ($app) {
            return $app['security']->getToken()->getUser();
        };

        $app['dispatcher'] = function () use ($app) {
            return $app->getParentContainer()->get('event_dispatcher');
        };

        $app['translator'] = function () use ($app) {
            return $app->getParentContainer()->get('translator');
        };

        $app['eccube.event.dispatcher'] = function () use ($app) {
            return $app['dispatcher'];
        };
    }

    /**
     * {@inheritdoc}
     */
    public function boot(Container $pimple)
    {
    }
}
