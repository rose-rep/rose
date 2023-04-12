<?php
namespace Citfact\SiteCore\Ajax;

session_start();

use Bitrix\Main\Application;
use Citfact\SiteCore\Tools\Basket as Basket;

class Dispatcher
{
    function __construct()
    {
        $request = Application::getInstance()->getContext()->getRequest();
        $action = $request->getPost('action');
        $itemId = (int) $request->getPost('item');
        $items = $request->getPost('ITEMS');
        $quantity = floatval($request->getPost('quantity'));
        $result = null;

        switch ($action) {
            /// Добавление в корзину
            case 'add2basket':
                $result = Basket::addToBasket($itemId, $quantity);
                break;

            /// Добавление в отложенные
            case 'add2delay':
                $result = Basket::addToDelay($itemId, $quantity);
                break;

            /// Множественное добавление в корзину (каталог таблицей)
            case 'add2basketMultiple':
                $result = Basket::addToCartMultiple($items);
                break;
        }

        echo json_encode($result);
    }
}