<?php

namespace Citfact\SiteCore\EventListener;

use Citfact\SiteCore\Tools\Event\SubscriberInterface;

class UserSubscriber implements SubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            // ['module' => 'main', 'event' => 'onBeforeUserLoginByHttpAuth', 'sort' => 100, 'method' => 'disableHttpAuth'],
        ];
    }

    public static function disableHttpAuth(&$arAuth)
    {
        return false;
    }
}
