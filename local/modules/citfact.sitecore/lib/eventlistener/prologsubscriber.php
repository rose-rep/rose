<?php

namespace Citfact\SiteCore\EventListener;

use Bitrix\Main\Application;

use Citfact\SiteCore\Tools\Event\SubscriberInterface;
use Citfact\SiteCore\Ajax\Dispatcher;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class PrologSubscriber implements SubscriberInterface
{

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            // ['module' => 'main', 'event' => 'OnBeforeProlog', 'method' => 'handleAjax'],
        ];
    }


    public static function handleAjax()
    {
        // Обработка AJAX-запросов
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            $app = Application::getInstance();
            $request = $app->getContext()->getRequest();

            if ($request->getPost('IS_AJAX_ACTION') == 'Y') {
                $ajaxDispatcher = new Dispatcher();
                die();
            }
        }
    }
}
