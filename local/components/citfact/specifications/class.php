<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Citfact\SiteCore\Core;
use Bitrix\Main\IO\Directory;
use Bitrix\Main\Application;

Loc::loadMessages(__FILE__);

class SpecificationsComponent extends \CBitrixComponent
{
    /**
     * @throws Exception
     */
    public function executeComponent()
    {
        global $USER, $APPLICATION;
        $this->request = Application::getInstance()->getContext()->getRequest();

        if ($this->request->get('downloadExcel') == 'wallpapers') {
            $APPLICATION->RestartBuffer();
            $this->downloadWallpapers();
            die();
        }

        if (empty($this->arParams['HL_BLOCK'])) {
            throw new Exception("Не указан параметр HL_BLOCK");
        }

        $this->prepareFilterData();

        $this->arResult['ITEMS'] = $this->getSpecifications(true);

        $this->IncludeComponentTemplate();

        return $this->arResult;
    }

    private function prepareFilter()
    {
        $this->arFilter = [];

        if ($nameFilter = $this->checkNameFilter()) {
            $this->arFilter[] = $nameFilter;
        }

        if (!empty($this->request->get('collection'))) {
            $this->arFilter[] = ['UF_COLLECTION' => $this->request->get('collection')];
        }
    }

    private function checkNameFilter() {
        if (!empty($this->request->get('wallpaperName'))) {
            $name = $this->request->get('wallpaperName');
            $translName = $this->translite($name);
            return
                [
                    'LOGIC' => 'OR',
                    ['%UF_NAME' => $name],
                    ['%UF_ARTICLE' => $name],
                    ['%UF_COLLECTION' => $name],
                    ['%UF_NAME' => $translName],
                    ['%UF_ARTICLE' => $translName],
                    ['%UF_COLLECTION' => $translName],
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
        $specifications = $this->arParams['HL_BLOCK'];

        $specificationList = $specifications::getList([
            'select' => ['UF_COLLECTION'],
        ]);

        $this->arResult['COLLECTIONS'] = [];

        while ($item = $specificationList->fetch()) {
            if (!in_array($item['UF_COLLECTION'], $this->arResult['COLLECTIONS'])) {
                $this->arResult['COLLECTIONS'][] = $item['UF_COLLECTION'];
            }
        }
    }

    private function downloadWallpapers()
    {
        Loader::includeModule("nkhost.phpexcel");

        global $PHPEXCELPATH;

        require_once($PHPEXCELPATH . '/PHPExcel.php');
        require_once($PHPEXCELPATH . '/PHPExcel/Writer/Excel5.php');

        $xls = new PHPExcel();

        $xls->setActiveSheetIndex(0);

        $sheet = $xls->getActiveSheet();

        $sheet->setCellValue("A1", LANG_ID == 'RU' ? 'Артикул' : 'Article');
        $sheet->setCellValue("B1", LANG_ID == 'RU' ? 'Коллекция' : 'Collection');
        $sheet->setCellValue("C1", LANG_ID == 'RU' ? 'Размер, м' : 'Size, m');
        $sheet->setCellValue("D1", LANG_ID == 'RU' ? 'Страна происхождения' : 'Country of origin');
        $sheet->setCellValue("E1", LANG_ID == 'RU' ? 'Наименование' : 'Name');
        $sheet->setCellValue("F1", LANG_ID == 'RU' ? 'Цвет' : 'Color');
        $sheet->setCellValue("G1", LANG_ID == 'RU' ? 'Штрих код рулона' : 'Roll barcode');
        $sheet->setCellValue("H1", LANG_ID == 'RU' ? 'Количество в коробке, рул.' : 'Quantity in a box, roll');
        $sheet->setCellValue("I1", LANG_ID == 'RU' ? 'Вес рулона, кг' : 'Roll weight, kg');
        $sheet->setCellValue("J1", LANG_ID == 'RU' ? 'Вес коробки, кг' : 'Box weight, kg');
        $sheet->setCellValue("K1", LANG_ID == 'RU' ? 'Объем коробки, данные фабрики, куб. м' : 'Box volume, factory data, cube. m');
        $sheet->setCellValue("L1", LANG_ID == 'RU' ? 'Кол-во на паллете, рул' : 'Quantity per pallet, roll');
        $sheet->setCellValue("M1", LANG_ID == 'RU' ? 'Штрихкод коробки' : 'Barcode of the box');

        $sheet->setCellValue("N1", LANG_ID == 'RU' ? 'Материал покрытия' : 'Coating material');
        $sheet->setCellValue("O1", LANG_ID == 'RU' ? 'Материал основания' : 'Base material');
        $sheet->setCellValue("P1", LANG_ID == 'RU' ? 'Раппорт' : 'Rapport');
        $sheet->setCellValue("Q1", LANG_ID == 'RU' ? 'Стыковка' : 'Docking');

        $sheet->setCellValue("R1", LANG_ID == 'RU' ? 'Архивный' : 'Archive');
        $sheet->setCellValue("S1", LANG_ID == 'RU' ? 'РРЦ' : 'RRP');

        foreach ($this->getSpecifications() as $key => $item) {
            $sheet->setCellValueByColumnAndRow(0, $key + 2, $item['UF_ARTICLE']);
            $sheet->setCellValueByColumnAndRow(1, $key + 2, $item['UF_COLLECTION']);
            $sheet->setCellValueByColumnAndRow(2, $key + 2, $item['UF_SIZE']);
            $sheet->setCellValueByColumnAndRow(3, $key + 2, $item['UF_COUNTRY']);
            $sheet->setCellValueByColumnAndRow(4, $key + 2, $item['UF_NAME']);
            $sheet->setCellValueByColumnAndRow(5, $key + 2, $item['UF_COLOR']);
            $sheet->setCellValueByColumnAndRow(6, $key + 2, $item['UF_RULON_BARCODE']);
            $sheet->setCellValueByColumnAndRow(7, $key + 2, $item['UF_BOX_AMOUNT']);
            $sheet->setCellValueByColumnAndRow(8, $key + 2, $item['UF_RULON_WEIGHT']);
            $sheet->setCellValueByColumnAndRow(9, $key + 2, $item['UF_BOX_WEIGHT']);
            $sheet->setCellValueByColumnAndRow(10, $key + 2, $item['UF_VOLUME']);
            $sheet->setCellValueByColumnAndRow(11, $key + 2, $item['UF_PALLET_AMOUNT']);
            $sheet->setCellValueByColumnAndRow(12, $key + 2, $item['UF_BOX_BARCODE']);
            $sheet->setCellValueByColumnAndRow(13, $key + 2, $item['UF_COATING']);
            $sheet->setCellValueByColumnAndRow(14, $key + 2, $item['UF_FOUNDATION']);
            $sheet->setCellValueByColumnAndRow(15, $key + 2, $item['UF_RAPPORT']);
            $sheet->setCellValueByColumnAndRow(16, $key + 2, $item['UF_COUPLING']);
            $sheet->setCellValueByColumnAndRow(17, $key + 2, $item['ARCHIVE']);
            $sheet->setCellValueByColumnAndRow(18, $key + 2, $item['PRICE']);
        }

        $objWriter = new PHPExcel_Writer_Excel5($xls);

        $fileName = md5('wallpapers' . time());
        $dirRel = "/upload/specifications/download/";
        $dir = Application::getDocumentRoot() . $dirRel;
        if (!Directory::isDirectoryExists($dir))
            Directory::createDirectory($dir);
        $objWriter->save($dir . "{$fileName}.xls");

        echo json_encode($dirRel . "{$fileName}.xls");
    }

    protected function getSpecifications($pagination = false) {
        $this->prepareFilter();

        $specifications = $this->arParams['HL_BLOCK'];

        $listData = [
            'select' => ['*'],
            'filter' => $this->arFilter,
            'order' => ['ID' => 'DESC']
        ];

        if ($pagination && $this->arParams['PAGE_SIZE'] >= 1) {
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

        $core = Core::getInstance();

        $catalogItems = \CIBlockElement::GetList(
            [],
            [
                "=IBLOCK_ID" => $core->getIblockId(Core::IBLOCK_CODE_CATALOG_ROSE_WALLPAPER),
                "=ACTIVE" => 'Y',
            ],
            false,
            false,
            [
                "ID",
                "PROPERTY_VENDOR_CODE",
                "PROPERTY_ARCHIVE",
            ]
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
        $arResult['SITE_PRICES'] = [];

        while ($item = $catalogItems->fetch()) {
            if ($item['PROPERTY_ARCHIVE_VALUE'] == 'Да') {
                $arResult['ARCHIVE'][] = $item['PROPERTY_VENDOR_CODE_VALUE'];
            }

            $arResult['PRICES'][$item['PROPERTY_VENDOR_CODE_VALUE']] = \CCurrencyLang::CurrencyFormat($productPricesList[$item['ID']],'RUB');
        }

        $arSpecifications = [];
        while ($item = $specificationList->fetch()) {
            unset($item['ID']);
            unset($item['UF_TRADEMARK']);
            $item = [
                'UF_ARTICLE' => $item['UF_ARTICLE'],
                'UF_COLLECTION' => $item['UF_COLLECTION'],
                'UF_SIZE' => $item['UF_SIZE'],
                'UF_COUNTRY' => $item['UF_COUNTRY'],
                'UF_NAME' => $item['UF_NAME'],
                'UF_COLOR' => $item['UF_COLOR'],
                'UF_RULON_BARCODE' => $item['UF_RULON_BARCODE'],
                'UF_BOX_AMOUNT' => $item['UF_BOX_AMOUNT'],
                'UF_RULON_WEIGHT' => $item['UF_RULON_WEIGHT'],
                'UF_BOX_WEIGHT' => $item['UF_BOX_WEIGHT'],
                'UF_VOLUME' => $item['UF_VOLUME'],
                'UF_PALLET_AMOUNT' => $item['UF_PALLET_AMOUNT'],
                'UF_BOX_BARCODE' => $item['UF_BOX_BARCODE'],
                'UF_COATING' => $item['UF_COATING'],
                'UF_FOUNDATION' => $item['UF_FOUNDATION'],
                'UF_RAPPORT' => $item['UF_RAPPORT'],
                'UF_COUPLING' => $item['UF_COUPLING'],
            ];
            $item['ARCHIVE'] = in_array($item['UF_ARTICLE'], $arResult['ARCHIVE'])
                ? Loc::getMessage("YES") : Loc::getMessage("NO");
            $item['PRICE'] = $arResult['PRICES'][$item['UF_ARTICLE']] ?? '-';

            $arSpecifications[] = $item;
        }
        return $arSpecifications;
    }
}