<?php

namespace Citfact\SiteCore\Tools;

use Bitrix\Highloadblock\HighloadBlockTable;

class HLBlock
{

    public function __construct()
    {
        \CModule::IncludeModule('highloadblock');
    }

    /**
     * @param $name
     * @param string $field
     * @return \Bitrix\Main\Entity\DataManager
     */
    public function getHlEntityByName($name, $field = 'NAME')
    {
        $filter = array($field => $name);
        $hlBlock = HighloadBlockTable::getList(array('filter' => $filter))->fetch();
        $obEntity = HighloadBlockTable::compileEntity($hlBlock);
        return $obEntity->getDataClass();
    }

    public function getHlEntityByTableName($name, $field = 'TABLE_NAME')
    {
        $filter = array($field => $name);
        $hlBlock = HighloadBlockTable::getList(array('filter' => $filter))->fetch();
        $obEntity = HighloadBlockTable::compileEntity($hlBlock);
        return $obEntity->getDataClass();
    }

    /**
     * @param $name
     * @param string $field
     * @return array|false
     * @throws \Bitrix\Main\ArgumentException
     */
    public function getHlDataByName($name, $field = 'NAME')
    {
        $filter = array($field => $name);
        return HighloadBlockTable::getList(array('filter' => $filter))->fetch();
    }
}