<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Citfact\SiteCore\Core;
use Citfact\SiteCore\Tools\HLBlock;

$core = Core::getInstance();

/*$catalogItems = \CIBlockElement::GetList(
    [],
    [
        "=IBLOCK_ID" => $core->getIblockId(Core::IBLOCK_CODE_CATALOG_STENOVA_WALLPAPER),
        "=ACTIVE" => 'Y',
    ],
    false,
    false,
    [
        "ID",
        "PROPERTY_VENDOR_CODE",
        "PROPERTY_ARCHIVE",

        "PROPERTY_stenova_wallpaper__material_pokr",
        "PROPERTY_stenova_wallpaper__material_osn",
        "PROPERTY_stenova_wallpaper__rapport_size",
        "PROPERTY_stenova_wallpaper__properties",
    ]
);

$productPrices = \Bitrix\Catalog\PriceTable::getList([
    "select" => ["PRODUCT_ID", "PRICE"],
    "filter" => ["CATALOG_GROUP_ID" => Core::PRICE_MIN_RETAIL_ID]
]);

$productPricesList = [];
while ($price = $productPrices->fetch()) {
    $productPricesList[$price['PRODUCT_ID']] = $price['PRICE'];
}*/

$arResult['ARCHIVE'] = []; // Свойство "Архивный"

$arResult['COATING_MATERIAL'] = []; // Свойство "Материал покрытия"
$arResult['BASE'] = []; // Свойство "Основа"
$arResult['RAPPORT'] = []; // Свойство "Раппорт"
$arResult['DOCKING'] = []; // Свойство "Стыковка"

$arResult['PRICES'] = [];
$arResult['SITE_PRICES'] = [];


/** Получим возможные значения для свойства "Свойства" */
/*$hl = new HLBlock();

$hlProperties = $hl->getHlEntityByName(Core::HL_BLOCK_WALLPAPERS_PROPERTY);
$propertyValues = $hlProperties::getList([
    'select' => ['UF_NAME', 'UF_XML_ID'],
    'filter' => ['UF_XML_ID' => Core::PROPERTY_DOCKING_VALUES],
]);

$propertyPropertiesValues = [];
while ($property = $propertyValues->fetch()) {
    $propertyPropertiesValues[$property['UF_XML_ID']] = $property['UF_NAME'];
}

while ($item = $catalogItems->fetch()) {
    if ($item['PROPERTY_ARCHIVE_VALUE'] == 'Да') {
        $arResult['ARCHIVE'][] = $item['PROPERTY_VENDOR_CODE_VALUE'];
    }

    $arResult['PRICES'][$item['PROPERTY_VENDOR_CODE_VALUE']] = \CCurrencyLang::CurrencyFormat($productPricesList[$item['ID']],'RUB');
*/

    /** Дополнительные свойства, не из таблицы с выгрузкой */
/*    if ($item['PROPERTY_STENOVA_WALLPAPER__MATERIAL_POKR_VALUE']) {
        $arResult['COATING_MATERIAL'][$item['PROPERTY_VENDOR_CODE_VALUE']] = $item['PROPERTY_STENOVA_WALLPAPER__MATERIAL_POKR_VALUE'];
    }

    if ($item['PROPERTY_STENOVA_WALLPAPER__MATERIAL_OSN_VALUE']) {
        $arResult['BASE'][$item['PROPERTY_VENDOR_CODE_VALUE']] = $item['PROPERTY_STENOVA_WALLPAPER__MATERIAL_OSN_VALUE'];
    }

    if ($item['PROPERTY_STENOVA_WALLPAPER__RAPPORT_SIZE_VALUE']) {
        $arResult['RAPPORT'][$item['PROPERTY_VENDOR_CODE_VALUE']] = $item['PROPERTY_STENOVA_WALLPAPER__RAPPORT_SIZE_VALUE'];
    }

    if ($item['PROPERTY_STENOVA_WALLPAPER__PROPERTIES_VALUE']) {
        if (isset($propertyPropertiesValues[$item['PROPERTY_STENOVA_WALLPAPER__PROPERTIES_VALUE']])) {
            $arResult['DOCKING'][$item['PROPERTY_VENDOR_CODE_VALUE']] = $propertyPropertiesValues[$item['PROPERTY_STENOVA_WALLPAPER__PROPERTIES_VALUE']];
        }
    }


    $siteItemPrice = round($productPricesList[$item['ID']] * Core::EXTRA_CHARGE);

    if ($siteItemPrice % 5) {
        $siteItemPrice += 5 - $siteItemPrice % 5;
    }

    $arResult['SITE_PRICES'][$item['PROPERTY_VENDOR_CODE_VALUE']] = \CCurrencyLang::CurrencyFormat($siteItemPrice,'RUB');
}
*/