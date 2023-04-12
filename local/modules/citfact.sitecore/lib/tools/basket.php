<?php

namespace Citfact\SiteCore\Tools;

class Basket
{
    public static function addToBasket($itemId, $quantity)
    {
        global $APPLICATION;
        $cBasket = new \CSaleBasket();
        $cElement = new \CIBlockElement();

        $dbBasketItems = $cBasket->GetList(
            ['NAME' => 'ASC', 'ID' => 'ASC'],
            ['PRODUCT_ID' => $itemId, 'FUSER_ID' => $cBasket->GetBasketUserID(), 'LID' => SITE_ID, 'ORDER_ID' => 'NULL'],
            false,
            false,
            ['ID', 'DELAY']
        )->Fetch();

        if (!empty($dbBasketItems) && $dbBasketItems['DELAY'] == 'Y') {
            $arFields = ['DELAY' => 'N', 'SUBSCRIBE' => 'N'];

            if (!empty($quantity)) {
                $arFields['QUANTITY'] = $quantity;
            }

            $cBasket->Update($dbBasketItems['ID'], $arFields);
        } else {
            $successfulAdd = true;
            $intProductIBlockID = (int) $cElement->GetIBlockByID($itemId);
            $strErrorExt = '';

            if (0 >= $intProductIBlockID) {
                $strError = 'CATALOG_ELEMENT_NOT_FOUND';
                $successfulAdd = false;
            }

            if (true === $successfulAdd) {
                $id = Add2BasketByProductID($itemId, $quantity);

                if (false === $id) {
                    if ($ex = $APPLICATION->GetException()) {
                        $strErrorExt = $ex->GetString();
                    }
                }

                $addResult = [
                    'STATUS' => 'OK',
                    'ITEM_ID' => $itemId,
                    'BASKET_PRICE' => self::getPrice(),
                    'BASKET_QUANTITY' => self::getQuantity(),
                    'MESSAGE' => 'CATALOG_SUCCESSFUL_ADD_TO_BASKET',
                    'MESSAGE_EXT' => $strErrorExt
                ];
            } else {
                $addResult = [
                    'STATUS' => 'ERROR',
                    'ITEM_ID' => $itemId,
                    'MESSAGE' => empty($strError) ?: $strError,
                    'MESSAGE_EXT' => $strErrorExt
                ];
            }
        }

        return empty($addResult) ?: $addResult;
    }

    public static function addToDelay($itemId, $quantity)
    {
        $cBasket = new \CSaleBasket();

        $successfulAdd = true;

        $dbBasketItems = $cBasket->GetList(
            ['NAME' => 'ASC', 'ID' => 'ASC'],
            [
                'PRODUCT_ID' => $itemId,
                'FUSER_ID' => $cBasket->GetBasketUserID(),
                'LID' => SITE_ID,
                'ORDER_ID' => 'NULL',
                'CAN_BUY' => 'Y',
                'SUBSCRIBE' => 'N'
            ],
            false,
            false,
            ['ID', 'PRODUCT_ID', 'DELAY']
        )->Fetch();

        if (!empty($dbBasketItems) && $dbBasketItems['DELAY'] == 'N') {
            $arFields = ['DELAY' => 'Y', 'SUBSCRIBE' => 'N'];

            if (!empty($quantity)) {
                $arFields['QUANTITY'] = $quantity;
            }

            $cBasket->Update($dbBasketItems['ID'], $arFields);
        } elseif (!empty($dbBasketItems) && $dbBasketItems['DELAY'] == 'Y') {
            $cBasket->Delete($dbBasketItems['ID']);
        } else {
            $id = Add2BasketByProductID($itemId, $quantity);

            if (false === $id) {
                global $APPLICATION;

                if ($ex = $APPLICATION->GetException()) {
                    $strErrorExt = $ex->GetString();
                }

                $successfulAdd = false;
                $strError = 'ERROR_ADD2BASKET';
            }

            $arFields = array('DELAY' => 'Y', 'SUBSCRIBE' => 'N');
            $cBasket->Update($id, $arFields);
        }

        if (true === $successfulAdd) {
            $dbBasketItemsRes = $cBasket->GetList(
                ['ID' => 'ASC'],
                [
                    'FUSER_ID' => $cBasket->GetBasketUserID(),
                    'LID' => SITE_ID,
                    'ORDER_ID' => 'NULL',
                    'CAN_BUY' => 'Y',
                    'DELAY' => 'Y'
                ],
                false,
                false,
                ['ID', 'PRODUCT_ID', 'DELAY']
            );

            $arItems = [];

            while ($arBasketItem = $dbBasketItemsRes->fetch()) {
                $arItems[] = $arBasketItem;
            }

            $countFavorites = count($arItems);

            $addResult = [
                'STATUS' => 'OK',
                'COUNT' => $countFavorites,
                'MESSAGE' => 'CATALOG_SUCCESSFUL_ADD_TO_BASKET',
                'MESSAGE_EXT' => empty($strErrorExt) ?: $strErrorExt,
            ];
        } else {
            $addResult = [
                'STATUS' => 'ERROR',
                'MESSAGE' => empty($strError) ?: $strError,
                'MESSAGE_EXT' => empty($strErrorExt) ?: $strErrorExt,
            ];
        }

        return $addResult;
    }

    public static function addToCartMultiple($items, $fields = [])
    {
        $addResultMultiple = [];
        $productsToBasket = [];

        if (!empty($items)) {
            foreach ($items as $item) {
                $productsToBasket[$item['ITEM_ID']] = [
                            'PRODUCT_ID' => $item['ITEM_ID'],
                            'QUANTITY'   => $item['QUANTITY'],
                        ];
            }
            
            $resultAdd = \Citfact\Sitecore\Order\Basket::addProducts($productsToBasket, $fields);

            if (!$resultAdd->isSuccess()) {
                $exceptionText = implode(' ,', $resultAdd->getErrorMessages());

                $addResultMultiple = [
                    'STATUS' => 'ERROR',
                    'MESSAGE' => 'ERROR_ADD2BASKET',
                    'MESSAGE_TEXT' => $exceptionText,
                    'INFO' => 'ERROR',
                ];

            } else {
                $addResultMultiple = [
                    'STATUS' => 'OK',
                    'MESSAGE' => 'CATALOG_SUCCESSFUL_UPDATE_BASKET',
                ];
            }
            
        }

        return $addResultMultiple;
    }

    public static function getCurrentCart()
    {
        return \Bitrix\Sale\Basket::loadItemsForFUser(
            \Bitrix\Sale\Fuser::getId(),
            \Bitrix\Main\Context::getCurrent()->getSite()
        );
    }

    public static function getPrice()
    {
        $basket = self::getCurrentCart();
        $basketItems = $basket->getBasketItems();
        $basketPrice = 0;

        foreach ($basketItems as $basketItem) {
            if (false === $basketItem->isDelay()) {
                $basketPrice += $basketItem->getFinalPrice();
            }
        }

        return $basketPrice;
    }

    public static function getQuantity()
    {
        $basket = self::getCurrentCart();
        $basketItems = $basket->getBasketItems();
        $quantity = 0;

        foreach ($basketItems as $basketItem) {
            if (false === $basketItem->isDelay()) {
                $quantity++;
            }
        }
        return $quantity;
    }
}