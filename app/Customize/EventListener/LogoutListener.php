<?php

namespace Customize\EventListener;

use Symfony\Component\Security\Http\Event\LogoutEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

class LogoutListener implements EventSubscriberInterface
{
    public function onLogout(LogoutEvent $event)
    {
        $request = $event->getRequest();
        $referer = $request->headers->get('referer');

        if (preg_match("/breeder/", $referer)) {
            $referer = "/breeder";
        } elseif (preg_match("/adoption/", $referer)) {
            $referer = "/adoption";
        }

        $response = new RedirectResponse($referer);
        $event->setResponse($response);
    }

    public static function getSubscribedEvents()
    {
        return [
            LogoutEvent::class => 'onLogout',
        ];
    }
}
