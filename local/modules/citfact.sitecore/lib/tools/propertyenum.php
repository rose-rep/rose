<?php

namespace Citfact\SiteCore\Tools;

class PropertyEnum
{
    public static function getEnumValues($iblockId, $propertyCode = ''): array
    {
        if ($iblockId == ''){
            throw new \Exception('Iblock id is empty');
        }

        if ($propertyCode == ''){
            throw new \Exception('Property code is empty');
        }

        $enums = [];
        $propertyEnums = \CIBlockPropertyEnum::GetList([], ['IBLOCK_ID' => $iblockId, 'CODE' => $propertyCode]);
        while ($enumFields = $propertyEnums->GetNext()) {
            $enums[$enumFields['PROPERTY_CODE']]['PROPERTY_NAME'] = $enumFields['PROPERTY_NAME'];
            $enums[$enumFields['PROPERTY_CODE']]['PROPERTY_CODE'] = $enumFields['PROPERTY_CODE'];

            $enums[$enumFields['PROPERTY_CODE']]['VALUES'][$enumFields['ID']] = [
                'ID' => $enumFields['ID'],
                'VALUE' => $enumFields['VALUE'],
                'XML_ID' => $enumFields['XML_ID']
            ];
        }

        return $enums;
    }
}