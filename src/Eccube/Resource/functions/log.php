<?php

use Psr\Log\LoggerInterface;
use Eccube\DependencyInjection\Facade\LoggerFacade;

function log_emergency($message, array $context = [])
{
    $logger = LoggerFacade::create();
    $logger->emergency($message, $context);
}

function log_alert($message, array $context = [])
{
    $logger = LoggerFacade::create();
    $logger->alert($message, $context);
}

function log_critical($message, array $context = [])
{
    $logger = LoggerFacade::create();
    $logger->critical($message, $context);
}

function log_error($message, array $context = [])
{
    $logger = LoggerFacade::create();
    $logger->error($message, $context);
}

function log_warning($message, array $context = [])
{
    $logger = LoggerFacade::create();
    $logger->warning($message, $context);
}

function log_notice($message, array $context = [])
{
    $logger = LoggerFacade::create();
    $logger->notice($message, $context);
}

/**
 * ログ出力.
 *
 * @param string $message
 * @param array $context
 * @param string $level
 */
function log_info($message, array $context = [], $level = 'info')
{
    static $logger;

    if (null === $logger) {
        $container = \Eccube\Application::getInstance()->getContainer();
        /** @var LoggerInterface $logger */
        $logger = $container->get('monolog.logger.eccube');
    }

    $logger->log($level, $message, $context);
}

function log_debug($message, array $context = [])
{
    $logger = LoggerFacade::create();
    $logger->debug($message, $context);
}

/**
 * プラグイン用ログ出力関数
 *
 * @param string $channel 設定されたchannel名
 *
 * @return Symfony\Bridge\Monolog\Logger
 */
function logs($channel)
{
    return LoggerFacade::getLoggerBy($channel);
}