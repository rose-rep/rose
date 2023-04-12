<?php

namespace Citfact\SiteCore\ProductSpecifications;

use Bitrix\Highloadblock as HL;
use Bitrix\Main\Loader;
use Citfact\SiteCore\Tools\HLBlock;
use CUserTypeEntity;

class Update
{
    const HL_SPECIFICATIONS_WALLPAPERS = 'SpecificationsWallpapers';
    const HL_SPECIFICATIONS_WALLPAPERS_TABLE = 'fact_specifications_wallpapers';

    public static function wallpapers($filePath = '')
    {
        if (!Loader::includeModule("nkhost.phpexcel")) {
            echo 'Установите модуль nkhost.phpexcel - https://marketplace.1c-bitrix.ru/solutions/nkhost.phpexcel/';
            die();
        };

        require_once($GLOBALS['PHPEXCELPATH'] . '/PHPExcel/IOFactory.php');

        /** Парсинг файла */

        $xls = \PHPExcel_IOFactory::load($filePath);

        $xls->setActiveSheetIndex(0);

        $sheet = $xls->getActiveSheet();

        for ($i = 2; $i <= $sheet->getHighestRow(); $i++) {
            $nColumn = \PHPExcel_Cell::columnIndexFromString($sheet->getHighestColumn());

            for ($j = 0; $j < $nColumn; $j++) {
                $arItems[$i][$j] = trim($sheet->getCellByColumnAndRow($j, $i)->getValue());
            }
        }

        $specificationWallpapers = self::getHigloadBlock();

        if (!$specificationWallpapers) return;

        /** Предварительно очищаем HL */
        $specifications = $specificationWallpapers::getList([
            'select' => ['ID']
        ]);

        while ($item = $specifications->fetch()) {
            $specificationWallpapers::delete($item['ID']);
        }

        /** Перезаписываем все значения */
        foreach ($arItems as $item) {
            if ($item[0] && $item[1]) {
                $specificationWallpapers::add([
                    'UF_ARTICLE' => $item[0],
                    'UF_NAME' => $item[1],
                    'UF_TRADEMARK' => $item[2],
                    'UF_COLLECTION' => $item[3],
                    'UF_SIZE' => $item[4],
                    'UF_COUNTRY' => $item[5],
                    'UF_COLOR' => $item[6],
                    'UF_RULON_BARCODE' => $item[7],
                    'UF_BOX_AMOUNT' => $item[8],
                    'UF_RULON_WEIGHT' => $item[9],
                    'UF_BOX_WEIGHT' => $item[10],
                    'UF_VOLUME' => $item[11],
                    'UF_PALLET_AMOUNT' => $item[12],
                    'UF_BOX_BARCODE' => $item[13],
                    'UF_COATING' => $item[14],
                    'UF_FOUNDATION' => $item[15],
                    'UF_RAPPORT' => $item[16],
                    'UF_COUPLING' => $item[17],
                ]);
            }
        }
    }

    public static function getHigloadBlock() {
        $hl = new HLBlock();

        $specificationWallpapers = $hl->getHlEntityByName(self::HL_SPECIFICATIONS_WALLPAPERS);

        if ($specificationWallpapers) {
            return $specificationWallpapers;
        } else {
            return self::createHigloadBlock();
        }
    }

    protected static function createHigloadBlock() {
        Loader::IncludeModule('highloadblock');

        $arLangs = Array(
            'ru' => 'Технические характеристики обоев',
            'en' => 'Wallpaper specifications'
        );

        $result = HL\HighloadBlockTable::add(array(
            'NAME' => self::HL_SPECIFICATIONS_WALLPAPERS,
            'TABLE_NAME' => self::HL_SPECIFICATIONS_WALLPAPERS_TABLE,
        ));

        if ($result->isSuccess()) {
            $id = $result->getId();
            foreach($arLangs as $lang_key => $lang_val){
                HL\HighloadBlockLangTable::add(array(
                    'ID' => $id,
                    'LID' => $lang_key,
                    'NAME' => $lang_val
                ));
            }

            $UFObject = 'HLBLOCK_'.$id;

            $arCartFields = [
                'UF_ARTICLE'=>[
                    'ENTITY_ID' => $UFObject,
                    'FIELD_NAME' => 'UF_ARTICLE',
                    'USER_TYPE_ID' => 'string',
                    'MANDATORY' => '',
                    "EDIT_FORM_LABEL" => ['ru'=>'Артикул', 'en'=>'Article'],
                    "LIST_COLUMN_LABEL" => ['ru'=>'Артикул', 'en'=>'Article'],
                    "LIST_FILTER_LABEL" => ['ru'=>'Артикул', 'en'=>'Article'],
                    "ERROR_MESSAGE" => ['ru'=>'', 'en'=>''],
                    "HELP_MESSAGE" => ['ru'=>'', 'en'=>''],
                ],
                'UF_NAME'=>[
                    'ENTITY_ID' => $UFObject,
                    'FIELD_NAME' => 'UF_NAME',
                    'USER_TYPE_ID' => 'string',
                    'MANDATORY' => '',
                    "EDIT_FORM_LABEL" => ['ru'=>'Название', 'en'=>'Name'],
                    "LIST_COLUMN_LABEL" => ['ru'=>'Название', 'en'=>'Name'],
                    "LIST_FILTER_LABEL" => ['ru'=>'Название', 'en'=>'Name'],
                    "ERROR_MESSAGE" => ['ru'=>'', 'en'=>''],
                    "HELP_MESSAGE" => ['ru'=>'', 'en'=>''],
                ],
                'UF_TRADEMARK'=>[
                    'ENTITY_ID' => $UFObject,
                    'FIELD_NAME' => 'UF_TRADEMARK',
                    'USER_TYPE_ID' => 'string',
                    'MANDATORY' => '',
                    "EDIT_FORM_LABEL" => ['ru'=>'Торговая марка', 'en'=>'Trademark'],
                    "LIST_COLUMN_LABEL" => ['ru'=>'Торговая марка', 'en'=>'Trademark'],
                    "LIST_FILTER_LABEL" => ['ru'=>'Торговая марка', 'en'=>'Trademark'],
                    "ERROR_MESSAGE" => ['ru'=>'', 'en'=>''],
                    "HELP_MESSAGE" => ['ru'=>'', 'en'=>''],
                ],
                'UF_COLLECTION'=>[
                    'ENTITY_ID' => $UFObject,
                    'FIELD_NAME' => 'UF_COLLECTION',
                    'USER_TYPE_ID' => 'string',
                    'MANDATORY' => '',
                    "EDIT_FORM_LABEL" => ['ru'=>'Коллекция', 'en'=>'Collection'],
                    "LIST_COLUMN_LABEL" => ['ru'=>'Коллекция', 'en'=>'Collection'],
                    "LIST_FILTER_LABEL" => ['ru'=>'Коллекция', 'en'=>'Collection'],
                    "ERROR_MESSAGE" => ['ru'=>'', 'en'=>''],
                    "HELP_MESSAGE" => ['ru'=>'', 'en'=>''],
                ],
                'UF_SIZE'=>[
                    'ENTITY_ID' => $UFObject,
                    'FIELD_NAME' => 'UF_SIZE',
                    'USER_TYPE_ID' => 'string',
                    'MANDATORY' => '',
                    "EDIT_FORM_LABEL" => ['ru'=>'Размер м', 'en'=>'Size'],
                    "LIST_COLUMN_LABEL" => ['ru'=>'Размер м', 'en'=>'Size'],
                    "LIST_FILTER_LABEL" => ['ru'=>'Размер м', 'en'=>'Size'],
                    "ERROR_MESSAGE" => ['ru'=>'', 'en'=>''],
                    "HELP_MESSAGE" => ['ru'=>'', 'en'=>''],
                ],
                'UF_COUNTRY'=>[
                    'ENTITY_ID' => $UFObject,
                    'FIELD_NAME' => 'UF_COUNTRY',
                    'USER_TYPE_ID' => 'string',
                    'MANDATORY' => '',
                    "EDIT_FORM_LABEL" => ['ru'=>'Страна происхождения', 'en'=>'Country'],
                    "LIST_COLUMN_LABEL" => ['ru'=>'Страна происхождения', 'en'=>'Country'],
                    "LIST_FILTER_LABEL" => ['ru'=>'Страна происхождения', 'en'=>'Country'],
                    "ERROR_MESSAGE" => ['ru'=>'', 'en'=>''],
                    "HELP_MESSAGE" => ['ru'=>'', 'en'=>''],
                ],
                'UF_COLOR'=>[
                    'ENTITY_ID' => $UFObject,
                    'FIELD_NAME' => 'UF_COLOR',
                    'USER_TYPE_ID' => 'string',
                    'MANDATORY' => '',
                    "EDIT_FORM_LABEL" => ['ru'=>'Цвет', 'en'=>'Color'],
                    "LIST_COLUMN_LABEL" => ['ru'=>'Цвет', 'en'=>'Color'],
                    "LIST_FILTER_LABEL" => ['ru'=>'Цвет', 'en'=>'Color'],
                    "ERROR_MESSAGE" => ['ru'=>'', 'en'=>''],
                    "HELP_MESSAGE" => ['ru'=>'', 'en'=>''],
                ],
                'UF_RULON_BARCODE'=>[
                    'ENTITY_ID' => $UFObject,
                    'FIELD_NAME' => 'UF_RULON_BARCODE',
                    'USER_TYPE_ID' => 'string',
                    'MANDATORY' => '',
                    "EDIT_FORM_LABEL" => ['ru'=>'Штрих-код рулона', 'en'=>'Rulon barcode'],
                    "LIST_COLUMN_LABEL" => ['ru'=>'Штрих-код рулона', 'en'=>'Rulon barcode'],
                    "LIST_FILTER_LABEL" => ['ru'=>'Штрих-код рулона', 'en'=>'Rulon barcode'],
                    "ERROR_MESSAGE" => ['ru'=>'', 'en'=>''],
                    "HELP_MESSAGE" => ['ru'=>'', 'en'=>''],
                ],
                'UF_BOX_AMOUNT'=>[
                    'ENTITY_ID' => $UFObject,
                    'FIELD_NAME' => 'UF_BOX_AMOUNT',
                    'USER_TYPE_ID' => 'string',
                    'MANDATORY' => '',
                    "EDIT_FORM_LABEL" => ['ru'=>'Количество в коробке, рул.', 'en'=>'Amount in box'],
                    "LIST_COLUMN_LABEL" => ['ru'=>'Количество в коробке, рул.', 'en'=>'Amount in box'],
                    "LIST_FILTER_LABEL" => ['ru'=>'Количество в коробке, рул.', 'en'=>'Amount in box'],
                    "ERROR_MESSAGE" => ['ru'=>'', 'en'=>''],
                    "HELP_MESSAGE" => ['ru'=>'', 'en'=>''],
                ],
                'UF_RULON_WEIGHT'=>[
                    'ENTITY_ID' => $UFObject,
                    'FIELD_NAME' => 'UF_RULON_WEIGHT',
                    'USER_TYPE_ID' => 'string',
                    'MANDATORY' => '',
                    "EDIT_FORM_LABEL" => ['ru'=>'Вес рулона, кг', 'en'=>'Rulon weight'],
                    "LIST_COLUMN_LABEL" => ['ru'=>'Вес рулона, кг', 'en'=>'Rulon weight'],
                    "LIST_FILTER_LABEL" => ['ru'=>'Вес рулона, кг', 'en'=>'Rulon weight'],
                    "ERROR_MESSAGE" => ['ru'=>'', 'en'=>''],
                    "HELP_MESSAGE" => ['ru'=>'', 'en'=>''],
                ],
                'UF_BOX_WEIGHT'=>[
                    'ENTITY_ID' => $UFObject,
                    'FIELD_NAME' => 'UF_BOX_WEIGHT',
                    'USER_TYPE_ID' => 'string',
                    'MANDATORY' => '',
                    "EDIT_FORM_LABEL" => ['ru'=>'Вес коробки', 'en'=>'Box weight'],
                    "LIST_COLUMN_LABEL" => ['ru'=>'Вес коробки', 'en'=>'Box weight'],
                    "LIST_FILTER_LABEL" => ['ru'=>'Вес коробки', 'en'=>'Box weight'],
                    "ERROR_MESSAGE" => ['ru'=>'', 'en'=>''],
                    "HELP_MESSAGE" => ['ru'=>'', 'en'=>''],
                ],
                'UF_VOLUME'=>[
                    'ENTITY_ID' => $UFObject,
                    'FIELD_NAME' => 'UF_VOLUME',
                    'USER_TYPE_ID' => 'string',
                    'MANDATORY' => '',
                    "EDIT_FORM_LABEL" => ['ru'=>'Объем коробки, данные фабрики, куб.м', 'en'=>'Box volume'],
                    "LIST_COLUMN_LABEL" => ['ru'=>'Объем коробки, данные фабрики, куб.м', 'en'=>'Box volume'],
                    "LIST_FILTER_LABEL" => ['ru'=>'Объем коробки, данные фабрики, куб.м', 'en'=>'Box volume'],
                    "ERROR_MESSAGE" => ['ru'=>'', 'en'=>''],
                    "HELP_MESSAGE" => ['ru'=>'', 'en'=>''],
                ],
                'UF_PALLET_AMOUNT'=>[
                    'ENTITY_ID' => $UFObject,
                    'FIELD_NAME' => 'UF_PALLET_AMOUNT',
                    'USER_TYPE_ID' => 'string',
                    'MANDATORY' => '',
                    "EDIT_FORM_LABEL" => ['ru'=>'Кол-во на паллете, рул', 'en'=>'Pallet amount'],
                    "LIST_COLUMN_LABEL" => ['ru'=>'Кол-во на паллете, рул', 'en'=>'Pallet amount'],
                    "LIST_FILTER_LABEL" => ['ru'=>'Кол-во на паллете, рул', 'en'=>'Pallet amount'],
                    "ERROR_MESSAGE" => ['ru'=>'', 'en'=>''],
                    "HELP_MESSAGE" => ['ru'=>'', 'en'=>''],
                ],
                'UF_BOX_BARCODE'=>[
                    'ENTITY_ID' => $UFObject,
                    'FIELD_NAME' => 'UF_BOX_BARCODE',
                    'USER_TYPE_ID' => 'string',
                    'MANDATORY' => '',
                    "EDIT_FORM_LABEL" => ['ru'=>'Штрихкод коробки', 'en'=>'Box barcode'],
                    "LIST_COLUMN_LABEL" => ['ru'=>'Штрихкод коробки', 'en'=>'Box barcode'],
                    "LIST_FILTER_LABEL" => ['ru'=>'Штрихкод коробки', 'en'=>'Box barcode'],
                    "ERROR_MESSAGE" => ['ru'=>'', 'en'=>''],
                    "HELP_MESSAGE" => ['ru'=>'', 'en'=>''],
                ],
                'UF_COATING'=>[
                    'ENTITY_ID' => $UFObject,
                    'FIELD_NAME' => 'UF_COATING',
                    'USER_TYPE_ID' => 'string',
                    'MANDATORY' => '',
                    "EDIT_FORM_LABEL" => ['ru'=>'Материал покрытия', 'en'=>'Coating'],
                    "LIST_COLUMN_LABEL" => ['ru'=>'Материал покрытия', 'en'=>'Coating'],
                    "LIST_FILTER_LABEL" => ['ru'=>'Материал покрытия', 'en'=>'Coating'],
                    "ERROR_MESSAGE" => ['ru'=>'', 'en'=>''],
                    "HELP_MESSAGE" => ['ru'=>'', 'en'=>''],
                ],
                'UF_FOUNDATION'=>[
                    'ENTITY_ID' => $UFObject,
                    'FIELD_NAME' => 'UF_FOUNDATION',
                    'USER_TYPE_ID' => 'string',
                    'MANDATORY' => '',
                    "EDIT_FORM_LABEL" => ['ru'=>'Материал основания', 'en'=>'Foundation'],
                    "LIST_COLUMN_LABEL" => ['ru'=>'Материал основания', 'en'=>'Foundation'],
                    "LIST_FILTER_LABEL" => ['ru'=>'Материал основания', 'en'=>'Foundation'],
                    "ERROR_MESSAGE" => ['ru'=>'', 'en'=>''],
                    "HELP_MESSAGE" => ['ru'=>'', 'en'=>''],
                ],
                'UF_RAPPORT'=>[
                    'ENTITY_ID' => $UFObject,
                    'FIELD_NAME' => 'UF_RAPPORT',
                    'USER_TYPE_ID' => 'string',
                    'MANDATORY' => '',
                    "EDIT_FORM_LABEL" => ['ru'=>'Раппорт', 'en'=>'Rapport'],
                    "LIST_COLUMN_LABEL" => ['ru'=>'Раппорт', 'en'=>'Rapport'],
                    "LIST_FILTER_LABEL" => ['ru'=>'Раппорт', 'en'=>'Rapport'],
                    "ERROR_MESSAGE" => ['ru'=>'', 'en'=>''],
                    "HELP_MESSAGE" => ['ru'=>'', 'en'=>''],
                ],
                'UF_COUPLING'=>[
                    'ENTITY_ID' => $UFObject,
                    'FIELD_NAME' => 'UF_COUPLING',
                    'USER_TYPE_ID' => 'string',
                    'MANDATORY' => '',
                    "EDIT_FORM_LABEL" => ['ru'=>'Стыковка', 'en'=>'Coupling'],
                    "LIST_COLUMN_LABEL" => ['ru'=>'Стыковка', 'en'=>'Coupling'],
                    "LIST_FILTER_LABEL" => ['ru'=>'Стыковка', 'en'=>'Coupling'],
                    "ERROR_MESSAGE" => ['ru'=>'', 'en'=>''],
                    "HELP_MESSAGE" => ['ru'=>'', 'en'=>''],
                ],
            ];


            $arSavedFieldsRes = [];
            foreach($arCartFields as $arCartField){
                $obUserField  = new CUserTypeEntity;
                $ID = $obUserField->Add($arCartField);
                $arSavedFieldsRes[] = $ID;
            }

            $hl = new HLBlock();
            return $hl->getHlEntityByName(self::HL_SPECIFICATIONS_WALLPAPERS);
        } else {
            $errors = $result->getErrorMessages();
            var_dump($errors);
        }
    }
}