<?php

use Psr\Log\LoggerInterface;

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
