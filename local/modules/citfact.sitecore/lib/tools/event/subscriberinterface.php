<?php



namespace Citfact\SiteCore\Tools\Event;

interface SubscriberInterface
{
    /**
     * Returns an array of event names this subscriber wants to listen to.
     * @return array
     */
    public static function getSubscribedEvents();
}
