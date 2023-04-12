<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Citfact\SiteCore\Core;
use Citfact\SiteCore\Tools\HLBlock;

Loc::loadMessages(__FILE__);

class SpecificationsComponent extends \CBitrixComponent
{
    /**
     * @throws Exception
     */
    public function executeComponent()
    {
        global $USER, $APPLICATION;

        if ($_REQUEST['downloadExcel'] == 'wallpapers') {
            $APPLICATION->RestartBuffer();
            $this->downloadWallpapers();
            die();
        }

        if ($_REQUEST['downloadExcel'] == 'home') {
            $APPLICATION->RestartBuffer();
            $this->downloadHome();
            die();
        }

        if ($this->StartResultCache(false, $USER->GetGroups())) {
            if (empty($this->arParams['HL_BLOCK'])) {
                throw new Exception("Не указан параметр HL_BLOCK");
            }

            $this->prepareFilter();

            if ($this->arParams['TAB'] == 'WALLPAPERS') {
                $this->prepareFilterData();
            }

            $this->prepareData();

            $this->IncludeComponentTemplate();
        }

        return $this->arResult;
    }

    private function prepareFilter()
    {
        $this->arFilter = [];

        if ($nameFilter = $this->checkNameFilter()) {
            $this->arFilter[] = $nameFilter;
        }

        if (!empty($_GET['tradeMark'])) {
            $this->arFilter[] = ['UF_TRADEMARK' => $_GET['tradeMark']];
        }

        if (!empty($_GET['collection'])) {
            $this->arFilter[] = ['UF_COLLECTION' => $_GET['collection']];
        }
    }

    private function checkNameFilter() {
        if (!empty($_REQUEST['wallpaperName'])) {
            $name = $_REQUEST['wallpaperName'];
            $translName = $this->translite($name);
            return
                [
                    'LOGIC' => 'OR',
                    ['%UF_NAME' => $name],
                    ['%UF_ARTICLE' => $name],
                    ['%UF_COLLECTION' => $name],
                    ['%UF_TRADEMARK' => $name],
                    ['%UF_NAME' => $translName],
                    ['%UF_ARTICLE' => $translName],
                    ['%UF_COLLECTION' => $translName],
                    ['%UF_TRADEMARK' => $translName],
                ];
        }

        if (!empty($_REQUEST['homeName'])) {
            $name = $_REQUEST['homeName'];
            $translName = $this->translite($name);
            return [
                    'LOGIC' => 'OR',
                    ['%UF_NAME' => $name],
                    ['%UF_ARTICLE' => $name],
                    ['%UF_NAME' => $translName],
                    ['%UF_ARTICLE' => $translName]
            ];
        }
    }

    private function translite($str) {
        return str_replace(
            ['а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у',
                'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ь', 'ы', 'ъ', 'э', 'ю', 'я'],
            ['a', 'b', 'v', 'g', 'd', 'e', 'e', 'zh', 'z', 'i', 'y', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u',
                'f', 'h', 'c', 'ch', 'sh', 'sch', '', 'y', '', 'e', 'yu', 'ya'],
            mb_strtolower($str)
        );
    }

    private function prepareFilterData()
    {
        $hl = new HLBlock();

        $specifications = $this->arParams['HL_BLOCK'];

        $specificationList = $specifications::getList([
            'select' => ['UF_TRADEMARK']
        ]);

        $this->arResult['TRADE_MARKS'] = [];

        while ($item = $specificationList->fetch()) {
            if (!in_array($item['UF_TRADEMARK'], $this->arResult['TRADE_MARKS'])) {
                $this->arResult['TRADE_MARKS'][] = $item['UF_TRADEMARK'];
            }
        }

        $filter = [];
        if (!empty($_GET['tradeMark'])) {
            $filter = ['UF_TRADEMARK' => $_GET['tradeMark']];
        }

        $specificationList = $specifications::getList([
            'select' => ['UF_COLLECTION'],
            'filter' => $filter,
        ]);

        $this->arResult['COLLECTIONS'] = [];

        while ($item = $specificationList->fetch()) {
            if (!in_array($item['UF_COLLECTION'], $this->arResult['COLLECTIONS'])) {
                $this->arResult['COLLECTIONS'][] = $item['UF_COLLECTION'];
            }
        }
    }

    private function prepareData()
    {
        $hl = new HLBlock();

        $specifications = $this->arParams['HL_BLOCK'];

        $listData = [
            'select' => ['*'],
            'filter' => $this->arFilter,
            'order' => ['ID' => 'DESC']
        ];

        if ($this->arParams['PAGE_SIZE'] >= 1) {
            $pageSize = intval($this->arParams['PAGE_SIZE']);
            $listData['limit'] = $pageSize;
            if ($this->arParams['PAGE'] >= 1) {
                $listData['offset'] = (intval($this->arParams['PAGE'] - 1) * $pageSize);
            }
            if ($this->arParams['PAGE'] >= 2) {
                $this->arResult['IS_AJAX'] = true;
            }
        }

        $specificationList = $specifications::getList($listData);

        while ($item = $specificationList->fetch()) {
            unset($item['ID']);

            $this->arResult['ITEMS'][] = $item;
        }
    }

    public function onPrepareComponentParams($arParams): array
    {
        return array_merge($arParams, [
            'CACHE_TYPE' => $arParams['CACHE_TYPE'] ?? 'A',
            'CACHE_TIME' => $arParams['CACHE_TIME'] ?? 36000000
        ]);
    }

    private function downloadWallpapers()
    {
        $core = Core::getInstance();

        Loader::includeModule("nkhost.phpexcel");

        global $PHPEXCELPATH;

        require_once($PHPEXCELPATH . '/PHPExcel.php');
        require_once($PHPEXCELPATH . '/PHPExcel/Writer/Excel5.php');

        $filter = [];

        if ($nameFilter = $this->checkNameFilter()) {
            $filter[] = $nameFilter;
        }

        if (!empty($_REQUEST['tradeMark'])) {
            $filter[] = ['UF_TRADEMARK' => $_REQUEST['tradeMark']];
        }

        if (!empty($_REQUEST['collection'])) {
            $filter[] = ['UF_COLLECTION' => $_REQUEST['collection']];
        }

        $hl = new HLBlock();

        $specifications = $this->arParams['HL_BLOCK'];

        $specificationList = $specifications::getList([
            'select' => ['*'],
            'filter' => $filter,
            'order' => ['ID' => 'DESC']
        ]);

        $xls = new PHPExcel();

        $xls->setActiveSheetIndex(0);

        $sheet = $xls->getActiveSheet();

        $sheet->setCellValue("A1", LANG_ID == 'RU' ? 'Наименование' : 'Name');
        $sheet->setCellValue("B1", LANG_ID == 'RU' ? 'Артикул' : 'Article');
        $sheet->setCellValue("C1", LANG_ID == 'RU' ? 'Торговая марка' : 'Trademark');
        $sheet->setCellValue("D1", LANG_ID == 'RU' ? 'Коллекция' : 'Collection');
        $sheet->setCellValue("E1", LANG_ID == 'RU' ? 'Размер, м' : 'Size, m');
        $sheet->setCellValue("F1", LANG_ID == 'RU' ? 'Страна происхождения' : 'Country of origin');
        $sheet->setCellValue("G1", LANG_ID == 'RU' ? 'Цвет' : 'Color');
        $sheet->setCellValue("H1", LANG_ID == 'RU' ? 'Штрих код рулона' : 'Roll barcode');
        $sheet->setCellValue("I1", LANG_ID == 'RU' ? 'Количество в коробке, рул.' : 'Quantity in a box, roll');
        $sheet->setCellValue("J1", LANG_ID == 'RU' ? 'Вес рулона, кг' : 'Roll weight, kg');
        $sheet->setCellValue("K1", LANG_ID == 'RU' ? 'Вес коробки, кг' : 'Box weight, kg');
        $sheet->setCellValue("L1", LANG_ID == 'RU' ? 'Объем коробки, данные фабрики, куб. м' : 'Box volume, factory data, cube. m');
        $sheet->setCellValue("M1", LANG_ID == 'RU' ? 'Кол-во на паллете, рул' : 'Quantity per pallet, roll');
        $sheet->setCellValue("N1", LANG_ID == 'RU' ? 'Штрихкод коробки' : 'Barcode of the box');

        $sheet->setCellValue("O1", LANG_ID == 'RU' ? 'Материал покрытия' : 'Coating material');
        $sheet->setCellValue("P1", LANG_ID == 'RU' ? 'Материал основания' : 'Base material');
        $sheet->setCellValue("Q1", LANG_ID == 'RU' ? 'Раппорт' : 'Rapport');
        $sheet->setCellValue("R1", LANG_ID == 'RU' ? 'Стыковка' : 'Docking');

        $sheet->setCellValue("S1", LANG_ID == 'RU' ? 'Архивный' : 'Archive');
        $sheet->setCellValue("T1", LANG_ID == 'RU' ? 'РРЦ' : 'RRP');
        $sheet->setCellValue("U1", LANG_ID == 'RU' ? 'Розничная цена сайта' : 'Retail price of the site');

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

        $arResult['ARCHIVE'] = [];

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
        }*/

        while ($item = $catalogItems->fetch()) {
            if ($item['PROPERTY_ARCHIVE_VALUE'] == 'Да') {
                $arResult['ARCHIVE'][] = $item['PROPERTY_VENDOR_CODE_VALUE'];
            }

            $arResult['PRICES'][$item['PROPERTY_VENDOR_CODE_VALUE']] = \CCurrencyLang::CurrencyFormat($productPricesList[$item['ID']],'RUB');

            /** Дополнительные свойства, не из таблицы с выгрузкой */
            if ($item['PROPERTY_STENOVA_WALLPAPER__MATERIAL_POKR_VALUE']) {
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

        $arSpecifications = [];
        while ($item = $specificationList->fetch()) {
            unset($item['ID']);

            $arSpecifications[] = $item;
        }

        foreach ($arSpecifications as $key => $item) {
            $sheet->setCellValueByColumnAndRow(0, $key + 2, $item['UF_NAME']);
            $sheet->setCellValueByColumnAndRow(1, $key + 2, $item['UF_ARTICLE']);
            $sheet->setCellValueByColumnAndRow(2, $key + 2, $item['UF_TRADEMARK']);
            $sheet->setCellValueByColumnAndRow(3, $key + 2, $item['UF_COLLECTION']);
            $sheet->setCellValueByColumnAndRow(4, $key + 2, $item['UF_SIZE']);
            $sheet->setCellValueByColumnAndRow(5, $key + 2, $item['UF_COUNTRY']);
            $sheet->setCellValueByColumnAndRow(6, $key + 2, $item['UF_COLOR']);
            $sheet->setCellValueByColumnAndRow(7, $key + 2, $item['UF_BARCODE_ROLL']);
            $sheet->setCellValueByColumnAndRow(8, $key + 2, $item['UF_COUNT_ROLL']);
            $sheet->setCellValueByColumnAndRow(9, $key + 2, $item['UF_WEIGHT']);
            $sheet->setCellValueByColumnAndRow(10, $key + 2, $item['UF_BOX_WEIGHT']);
            $sheet->setCellValueByColumnAndRow(11, $key + 2, $item['UF_VOLUME']);
            $sheet->setCellValueByColumnAndRow(12, $key + 2, $item['UF_COUNT']);
            $sheet->setCellValueByColumnAndRow(13, $key + 2, $item['UF_BARCODE_BOX']);

            $sheet->setCellValueByColumnAndRow(14, $key + 2, $arResult['COATING_MATERIAL'][$item['UF_ARTICLE']] ?? '-');
            $sheet->setCellValueByColumnAndRow(15, $key + 2, $arResult['BASE'][$item['UF_ARTICLE']] ?? '-');
            $sheet->setCellValueByColumnAndRow(16, $key + 2, $arResult['RAPPORT'][$item['UF_ARTICLE']] ?? '-');
            $sheet->setCellValueByColumnAndRow(17, $key + 2, $arResult['DOCKING'][$item['UF_ARTICLE']] ?? '-');

            $sheet->setCellValueByColumnAndRow(18, $key + 2, in_array($item['UF_ARTICLE'], $arResult['ARCHIVE'])
                ? Loc::getMessage("YES") : Loc::getMessage("NO"));
            $sheet->setCellValueByColumnAndRow(19, $key + 2, $arResult['PRICES'][$item['UF_ARTICLE']] ?? '-');
            $sheet->setCellValueByColumnAndRow(20, $key + 2, $arResult['SITE_PRICES'][$item['UF_ARTICLE']] ?? '-');
        }

        $objWriter = new PHPExcel_Writer_Excel5($xls);

        $fileName = md5('wallpapers' . time());
        $pathRel = "/upload/specifications/download/{$fileName}.xls";
        $pathFull = $_SERVER['DOCUMENT_ROOT'] . $pathRel;
        $objWriter->save($pathFull);

        echo json_encode($pathRel);
    }

    private function downloadHome()
    {
        $core = Core::getInstance();

        Loader::includeModule("nkhost.phpexcel");

        global $PHPEXCELPATH;

        require_once($PHPEXCELPATH . '/PHPExcel.php');
        require_once($PHPEXCELPATH . '/PHPExcel/Writer/Excel5.php');

        $filter = [];

        if ($nameFilter = $this->checkNameFilter()) {
            $filter[] = $nameFilter;
        }

        $hl = new HLBlock();

        $specifications = $this->arParams['HL_BLOCK'];

        $specificationList = $specifications::getList([
            'select' => ['*'],
            'filter' => $filter,
            'order' => ['ID' => 'DESC']
        ]);

        $xls = new PHPExcel();

        $xls->setActiveSheetIndex(0);

        $sheet = $xls->getActiveSheet();

        $sheet->setCellValue("A1", LANG_ID == 'RU' ? 'Артикул' : 'Article');
        $sheet->setCellValue("B1", LANG_ID == 'RU' ? 'Наименование' : 'Name');
        $sheet->setCellValue("C1", LANG_ID == 'RU' ? 'Описание' : 'Description');
        $sheet->setCellValue("D1", LANG_ID == 'RU' ? 'Состав товара' : 'Product composition');
        $sheet->setCellValue("E1", LANG_ID == 'RU' ? 'Штрихкод 1 упаковки' : 'Barcode of 1 package');
        $sheet->setCellValue("F1", LANG_ID == 'RU' ? 'Штрихкод 2 упаковки' : 'Barcode 2 packages');
        $sheet->setCellValue("G1", LANG_ID == 'RU' ? 'Цвет' : 'Color');
        $sheet->setCellValue("H1", LANG_ID == 'RU' ? 'Размер изделия' : 'Product size');
        $sheet->setCellValue("I1", LANG_ID == 'RU' ? 'Вид упаковки' : 'Type of packaging');
        $sheet->setCellValue("J1", LANG_ID == 'RU' ? 'Вес упаковки, кг (брутто)' : 'Package weight, kg (gross)');
        $sheet->setCellValue("K1", LANG_ID == 'RU' ? 'Размер упаковки (см)' : 'Package size (cm)');
        $sheet->setCellValue("L1", LANG_ID == 'RU' ? 'Кратность' : 'Multiplicity');
        $sheet->setCellValue("M1", LANG_ID == 'RU' ? 'Вид транспортной упаковки' : 'Type of transport package');
        $sheet->setCellValue("N1", LANG_ID == 'RU' ? 'Страна происхождения' : 'Country of origin');
        $sheet->setCellValue("O1", LANG_ID == 'RU' ? 'Архивный' : 'Archive');
        $sheet->setCellValue("P1", LANG_ID == 'RU' ? 'РРЦ' : 'RRP');
        $sheet->setCellValue("Q1", LANG_ID == 'RU' ? 'Розничная цена сайта' : 'Retail price of the site');

        $arSpecifications = [];
        while ($item = $specificationList->fetch()) {
            unset($item['ID']);

            $arSpecifications[] = $item;
        }

        $catalogItems = \CIBlockElement::GetList(
            [],
            [
                "=IBLOCK_ID" => $core->getIblockId(Core::IBLOCK_CODE_CATALOG_STENOVA_HOME),
                "=ACTIVE" => 'Y',
            ],
            false,
            false,
            ["ID", "PROPERTY_VENDOR_CODE", "PROPERTY_ARCHIVE"]
        );

        $productPrices = \Bitrix\Catalog\PriceTable::getList([
            "select" => ["PRODUCT_ID", "PRICE"],
            "filter" => ["CATALOG_GROUP_ID" => Core::PRICE_MIN_RETAIL_ID]
        ]);

        $productPricesList = [];
        while ($price = $productPrices->fetch()) {
            $productPricesList[$price['PRODUCT_ID']] = $price['PRICE'];
        }

        $arResult['ARCHIVE'] = [];
        $arResult['PRICES'] = [];

        while ($item = $catalogItems->fetch()) {
            if ($item['PROPERTY_VENDOR_CODE_VALUE']) {
                if ($item['PROPERTY_ARCHIVE_VALUE'] == 'Да') {
                    $arResult['ARCHIVE'][] = $item['PROPERTY_VENDOR_CODE_VALUE'];
                }

                $arResult['PRICES'][intval($item['PROPERTY_VENDOR_CODE_VALUE'])] = \CCurrencyLang::CurrencyFormat($productPricesList[$item['ID']],'RUB');

                $siteItemPrice = $productPricesList[$item['ID']] * Core::EXTRA_CHARGE;

                if ($siteItemPrice % 5) {
                    $siteItemPrice += 5 - $siteItemPrice % 5;
                }

                $arResult['SITE_PRICES'][intval($item['PROPERTY_VENDOR_CODE_VALUE'])] = \CCurrencyLang::CurrencyFormat($siteItemPrice,'RUB');
            }
        }


        $catalogItems = \CIBlockElement::GetList(
            [],
            [
                "=IBLOCK_ID" => $core->getIblockId(Core::IBLOCK_CODE_CATALOG_STENOVA_HOME_PACKAGE),
                "=ACTIVE" => 'Y',
            ],
            false,
            false,
            ["ID", "PROPERTY_VENDOR_CODE", "PROPERTY_ARCHIVE_OFFER"]
        );

        while ($item = $catalogItems->fetch()) {
            if ($item['PROPERTY_VENDOR_CODE_VALUE']) {
                if ($item['PROPERTY_ARCHIVE_OFFER_VALUE'] == 'Да') {
                    $arResult['ARCHIVE'][] = $item['PROPERTY_VENDOR_CODE_VALUE'];
                }

                $arResult['PRICES'][intval($item['PROPERTY_VENDOR_CODE_VALUE'])] = \CCurrencyLang::CurrencyFormat($productPricesList[$item['ID']],'RUB');

                $siteItemPrice = $productPricesList[$item['ID']] * Core::EXTRA_CHARGE;

                if ($siteItemPrice % 5) {
                    $siteItemPrice += 5 - $siteItemPrice % 5;
                }

                $arResult['SITE_PRICES'][intval($item['PROPERTY_VENDOR_CODE_VALUE'])] = \CCurrencyLang::CurrencyFormat($siteItemPrice,'RUB');
            }
        }

        foreach ($arSpecifications as $key => $item) {
            $sheet->setCellValueByColumnAndRow(0, $key + 2, $item['UF_ARTICLE']);
            $sheet->setCellValueByColumnAndRow(1, $key + 2, $item['UF_NAME']);
            $sheet->setCellValueByColumnAndRow(2, $key + 2, $item['UF_DESCRIPTION']);
            $sheet->setCellValueByColumnAndRow(3, $key + 2, $item['UF_COMPOSITION']);
            $sheet->setCellValueByColumnAndRow(4, $key + 2, $item['UF_BARCODE_1']);
            $sheet->setCellValueByColumnAndRow(5, $key + 2, $item['UF_BARCODE_2']);
            $sheet->setCellValueByColumnAndRow(6, $key + 2, $item['UF_COLOR']);
            $sheet->setCellValueByColumnAndRow(7, $key + 2, $item['UF_PRODUCT_SIZE']);
            $sheet->setCellValueByColumnAndRow(8, $key + 2, $item['UF_PACKAGE_KIND']);
            $sheet->setCellValueByColumnAndRow(9, $key + 2, $item['UF_PACKAGE_WEIGHT']);
            $sheet->setCellValueByColumnAndRow(10, $key + 2, $item['UF_PACKAGE_SIZE_SM']);
            $sheet->setCellValueByColumnAndRow(11, $key + 2, $item['UF_MULTIPLICITY']);
            $sheet->setCellValueByColumnAndRow(12, $key + 2, $item['UF_TRANSPORT_PACKAGE_SIZE']);
            $sheet->setCellValueByColumnAndRow(13, $key + 2, $item['UF_COUNTRY']);
            $sheet->setCellValueByColumnAndRow(14, $key + 2, in_array($item['UF_ARTICLE'], $arResult['ARCHIVE'])
                ? Loc::getMessage("YES") : Loc::getMessage("NO"));
            $sheet->setCellValueByColumnAndRow(15, $key + 2, $arResult['PRICES'][$item['UF_ARTICLE']] ?? '-');
            $sheet->setCellValueByColumnAndRow(16, $key + 2, $arResult['SITE_PRICES'][$item['UF_ARTICLE']] ?? '-');
        }

        $objWriter = new PHPExcel_Writer_Excel5($xls);

        $fileName = md5('wallpapers' . time());
        $pathRel = "/upload/specifications/download/{$fileName}.xls";
        $pathFull = $_SERVER['DOCUMENT_ROOT'] . $pathRel;
        $objWriter->save($pathFull);

        echo json_encode($pathRel);
    }
}