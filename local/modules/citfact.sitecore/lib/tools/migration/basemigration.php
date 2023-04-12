<?

namespace Citfact\SiteCore\Tools\Migration;

use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Iblock\PropertyTable;

class BaseMigration
{
    private $iblockId = '';

    public function __construct()
    {
        \CModule::IncludeModule('main');
        \CModule::IncludeModule('catalog');
        \CModule::IncludeModule('iblock');
        \CModule::IncludeModule('sale');
        \CModule::IncludeModule('form');
    }

    public function getCsvData($path, $delimiter = '\\')
    {
        $result = [];
        $csvFile = new \CCSVData();
        $csvFile->LoadFile($_SERVER['DOCUMENT_ROOT'] . $path);
        $fieldCodes = $csvFile->Fetch();
        $fieldCodes = explode($delimiter, $fieldCodes[0]);
        while ($item = $csvFile->Fetch()) {
            $resultItem = [];
            $item = explode($delimiter, $item[0]);
            foreach ($item as $key => $data) {
                $resultItem[$fieldCodes[$key]] = $data;
            }
            $result[] = $resultItem;
        }
        return $result;
    }

    public function setWebForm($formData, $formFieldData = [], $webFormId = false)
    {
        if (!$formData['SID']) {
            pre('ERROR: setWebForm - no $formData[\'SID\']');
            return;
        }
        $form = new \CForm();

        $res = $form->GetList($by = 'ID', $order = 'ASC', ['SID' => $formData['SID']]);
        $webForm = $res->Fetch();
        if ($webForm) {
            $webFormId = $webForm['ID'];
        }
        $webFormId = $form->Set($formData, $webFormId);

        $this->setWebFormMailTemplate($formData, $webFormId);
        $this->setWebFormFields($formFieldData, $webFormId);
        $this->setWebFormStatus($webFormId);

        pre('setWebForm - success $webFormId=' . $webFormId);
    }

    public function setWebFormMailTemplate($formData, $webFormId)
    {
        $form = new \CForm();
        $mailTemplatesCount = ($formData['MAIL_TEMPLATES_COUNT']) ?: 1;
        $ids = $this->getAllWebFormMailTemplateIds($formData['SID']);
        if (!$ids) {
            for ($i = 0; $i < $mailTemplatesCount; $i++) {
                $form->SetMailTemplate($webFormId);
            }
            $ids = $this->getAllWebFormMailTemplateIds($formData['SID']);
        }
        $data = ['arMAIL_TEMPLATE' => $ids];
        $form->Set($data, $webFormId);
    }

    private function getAllWebFormMailTemplateIds($formSid)
    {
        $eventMessage = new \CEventMessage();
        $ids = [];
        $res = $eventMessage->GetList($by = 'ID', $order = 'ASC', ['EVENT_NAME' => 'FORM_FILLING_' . $formSid]);
        while ($item = $res->Fetch()) {
            if ($item) {
                $ids[] = $item['ID'];
            }
        }
        return $ids;
    }

    public function setWebFormFields($formFieldData, $webFormId)
    {
        if (!$formFieldData || !$webFormId) {
            return;
        }
        $formField = new \CFormField();
        $formAnswer = new \CFormAnswer();
        $formFieldDataByKey = [];
        foreach ($formFieldData as $item) {
            $item['ID'] = false;
            $item['FORM_ID'] = $webFormId;
            $formFieldDataByKey[$item['SID']] = $item;
        }
        $formFieldData = $formFieldDataByKey;
        $res = $formField->GetList($webFormId, 'ALL', $by = 'ID', $order = 'ASC', ['SID' => array_keys($formFieldData)]);
        while ($item = $res->Fetch()) {
            if (!$formFieldData[$item['SID']]) {
                $formField->Delete($item['ID']);
            }
            $formFieldData[$item['SID']]['ID'] = $item['ID'];
        }
        foreach ($formFieldData as &$item) {
            if (!$item['ID']) {
                continue;
            }
            $res = $formAnswer->GetList($item['ID'], $by = 'ID', $order = 'ASC', []);
            while ($answer = $res->Fetch()) {
                if ($item['arANSWER'][0]['ID']) {
                    $formAnswer->Delete($answer['ID']);
                } else {
                    $item['arANSWER'][0]['ID'] = $answer['ID'];
                }
            }
        }
        unset($item);

        foreach ($formFieldData as $data) {
            $formField->Set($data, $data['ID']);
        }
    }

    public function setWebFormStatus($webFormId)
    {
        if (!$webFormId) {
            return;
        }
        $formStatus = new \CFormStatus();
        $res = $formStatus->GetList($webFormId, $by = 'ID', $order = 'ASC', []);
        $webFormStatus = null;
        while ($status = $res->Fetch()) {
            if ($webFormStatus) {
                $formStatus->Delete($status['ID']);
            }
            $webFormStatus = $status;
        }
        if ($webFormStatus) {
            return;
        }
        $formStatus->Set([
            'FORM_ID' => $webFormId,
            'ACTIVE' => 'Y',
            'TITLE' => 'DEFAULT',
            'CSS' => 'statusgreen',
            'DEFAULT_VALUE' => 'Y',
        ]);
    }

    public function updateUserGroup($id, $fields)
    {
        $group = new \CGroup();
        $by = 'ID';
        $order = 'ASC';
        $item = $group->GetList($by, $order, array(
            'ID' => $id
        ))->Fetch();
        if ($item) {
            $group->Update($item['ID'], $fields);
            pre("updateUserGroup completed. ID=" . $item['ID']);
        } else {
            pre("updateUserGroup skipped.");
        }
    }

    private function getProperty($filter)
    {
        $iblockProperty = new \CIBlockProperty();
        $result = $iblockProperty->GetList(
            [],
            $filter
        );
        return $result->Fetch();
    }

    private function getSaleProperty($filter)
    {
        $saleProperty = new \CSaleOrderProps();
        $result = $saleProperty->GetList(
            [],
            $filter
        );
        return $result->Fetch();
    }

    public function setIblockProperty($properties)
    {
        $iblockProperty = new \CIBlockProperty();
        $iblockEnum = new \CIBlockPropertyEnum();
        foreach ($properties as $propertySettings) {
            if (!$propertySettings['IBLOCK_ID'] && $propertySettings['IBLOCK_CODE']) {
                $propertySettings['IBLOCK_ID'] = $this->getIblockIdByCode($propertySettings['IBLOCK_CODE']);
            }
            if (!$propertySettings['IBLOCK_ID']) {
                pre('setIblockProperty - empty IBLOCK_ID.');
                continue;
            }

            $filter = [
                'CODE' => $propertySettings['CODE'],
                'IBLOCK_ID' => $propertySettings['IBLOCK_ID'],
            ];
            $property = $this->getProperty($filter);
            if (!$property) {
                $iblockProperty->add($propertySettings);
                pre('setIblockProperty - $iblockProperty->LAST_ERROR add');
                pre($iblockProperty->LAST_ERROR);
                $property = $this->getProperty($filter);
            }
            if (!$property) {
                pre('setIblockProperty - property not found ' . $propertySettings['CODE']);
                continue;
            }
            $iblockProperty->Update($property['ID'], $propertySettings);
            pre('setIblockProperty - $iblockProperty->LAST_ERROR Update');
            pre($iblockProperty->LAST_ERROR);
            foreach ($propertySettings['ITEMS'] as $item) {
                if (!$item['XML_ID'] || !$item['VALUE']) {
                    pre('setIblockProperty - wrong XML_ID or VALUE');
                    pre($item);
                    continue;
                }
                $filter = [
                    'CODE' => $propertySettings['CODE'],
                    'IBLOCK_ID' => $property['IBLOCK_ID'],
                    'XML_ID' => $item['XML_ID'],
                    'PROPERTY_ID' => $property['ID'],
                ];
                $res = $iblockEnum->GetList([], $filter);
                $enumItem = $res->Fetch();
                if ($enumItem) {
                    pre('setIblockProperty. Enum item ' . $item['XML_ID'] . ' (' . $item['VALUE'] . ') already exists.');
                    continue;
                }
                $data = [
                    'CODE' => $propertySettings['CODE'],
                    'IBLOCK_ID' => $propertySettings['IBLOCK_ID'],
                    'XML_ID' => $item['XML_ID'],
                    'VALUE' => $item['VALUE'],
                    'PROPERTY_ID' => $property['ID'],
                ];
                $result = $iblockEnum->Add($data);
                pre('setIblockProperty. Enum item ' . $item['XML_ID'] . ' (' . $item['VALUE'] . ') added. ID=' . $result);
            }
        }
    }

    public function createIblockProperties($properties)
    {
        foreach ($properties as $property) {
            if (!$property['IBLOCK_ID']) {
                $property['IBLOCK_ID'] = $this->iblockId;
            }
            $property['ACTIVE'] = 'Y';
            $result = \CIBlockProperty::GetList(
                array(),
                [
                    'CODE' => $property['CODE']
                ]
            );

            if ($item = $result->Fetch()) {
                pre($item);
                pre("createAcceptPointProperties. property exists.");
            } else {
                $iblock = new \CIBlockProperty();
                $iblock->Add($property);
            }
        }
    }

    private function getIblockIdByCode($code)
    {
        $iblock = new \CIBlock();
        $res = $iblock->GetList([], [
            'CODE' => $code
        ]);
        $item = $res->Fetch();
        return $item['ID'];
    }

    public function deleteIblockItem($fields)
    {
        $element = new \CIBlockElement();
        $select = array(
            'ID',
        );
        if ($fields['IBLOCK_CODE'] && !$fields['IBLOCK_ID']) {
            $fields['IBLOCK_ID'] = $this->getIblockIdByCode($fields['IBLOCK_CODE']);
        }
        if ($fields['IBLOCK_ID']) {
            $filter = $fields;
            $res = $element->GetList([], $filter, false, false, $select);
            $item = $res->Fetch();
            if ($item) {
                $element->Delete($item['ID']);
                PRE('deleteIblockItem. Item Deleted. ' . $item['ID']);
            }
        }
    }

    public function setIblockItem($fields)
    {
        $section = new \CIBlockSection();
        $element = new \CIBlockElement();
        $iblock = new \CIBlock();
        $select = array(
            'ID',
        );
        $propertyData = $this->getPropertyData($fields);
        if ($fields['IBLOCK_CODE'] && !$fields['IBLOCK_ID']) {
            $res = $iblock->GetList([], [
                'CODE' => $fields['IBLOCK_CODE']
            ]);
            $item = $res->Fetch();
            $fields['IBLOCK_ID'] = $item['ID'];
        }
        if ($fields['SECTION_CODE'] && $fields['IBLOCK_ID']) {
            $res = $section->GetList([], [
                'IBLOCK_ID' => $fields['IBLOCK_ID'],
                'CODE' => $fields['SECTION_CODE'],
            ]);
            $item = $res->Fetch();
            if ($item) {
                $fields['IBLOCK_SECTION_ID'] = $item['ID'];
            }
        }
        if (($fields['ID'] || $fields['CODE']) && $fields['IBLOCK_ID']) {
            $filter = array(
                'IBLOCK_ID' => $fields['IBLOCK_ID'],
            );
            if ($fields['ID']) {
                $filter['ID'] = $fields['ID'];
            } elseif ($fields['CODE']) {
                $filter['CODE'] = $fields['CODE'];
            }
            $fieldData = $this->getFieldData($fields);
            $res = $element->GetList(array(), $filter, false, false, $select);
            $item = $res->Fetch();
            if ($item) {
                $element->Update($item['ID'], $fieldData);
                $element->SetPropertyValuesEx($item['ID'], $fields['IBLOCK_ID'], $propertyData);
                PRE('createIblockItem. Item already exists. ' . $fields['CODE']);
                return $item['ID'];
            }
        }
        $result = $element->Add($fields);
        if ($result) {
            $element->SetPropertyValuesEx($result, $fields['IBLOCK_ID'], $propertyData);
            PRE('createIblockItem. Success. $item[\'ID\']=' . $result);
            return $result;
        } else {
            PRE('createIblockItem error.' . $element->LAST_ERROR);
        }
        return '';
    }

    private function getFieldData($fields)
    {
        $result = [];
        foreach ($fields as $code => $item) {
            if (strpos($code, 'PROPERTY_') === 0) {
                continue;
            }
            $result[$code] = $item;
        }
        return $result;
    }

    private function getPropertyData($fields)
    {
        $result = [];
        foreach ($fields as $code => $item) {
            if (strpos($code, 'PROPERTY_') !== 0) {
                continue;
            }
            $code = str_replace('PROPERTY_', '', $code);
            $result[$code] = $item;
        }
        return $result;
    }

    public function setIblockSection($fields)
    {
        $iblockSection = new \CIBlockSection();
        $iblock = new \CIBlock();
        $select = array(
            'ID',
        );
        if ($fields['IBLOCK_CODE'] && !$fields['IBLOCK_ID']) {
            $res = $iblock->GetList([], [
                'CODE' => $fields['IBLOCK_CODE']
            ]);
            $item = $res->Fetch();
            $fields['IBLOCK_ID'] = $item['ID'];
        }
        if ($fields['CODE'] && $fields['IBLOCK_ID']) {
            $filter = array(
                'IBLOCK_ID' => $fields['IBLOCK_ID'],
                'CODE' => $fields['CODE'],
            );
            $res = $iblockSection->GetList(array(), $filter, false, $select);
            $item = $res->Fetch();
            if ($item) {
                $iblockSection->Update($item['ID'], $fields);
                PRE('createIblockSection. Item already exists. ' . $fields['CODE']);
                return $item['ID'];
            }
        }
        $result = $iblockSection->Add($fields);
        if ($result) {
            PRE('createIblockSection. Success. $item[\'ID\']=' . $result);
            return $result;
        } else {
            PRE('createIblockSection error.' . $iblockSection->LAST_ERROR);
        }
        return '';
    }

    private function getHlBlock($name, $tableName)
    {
        $hlBlockTable = new HighloadBlockTable();

        $res = $hlBlockTable->getList([
            'filter' => [
                'NAME' => $name,
                'TABLE_NAME' => $tableName,
            ]
        ]);
        return $res->fetch();
    }

    public function setIblock($data)
    {
        $iblock = new \CIBlock();
        $res = $iblock->GetList([], ['CODE' => $data['CODE']]);
        $item = $res->Fetch();
        if ($item) {
            $result = $iblock->Update($item['ID'], $data);
            pre('setIblock - Update');
            pre($result);
        } else {
            $result = $iblock->Add($data);
            pre('setIblock - Add');
            pre($result);
            pre($iblock->LAST_ERROR);
        }
    }

    public function createHlBlock($name, $tableName)
    {
        $hlBlockTable = new HighloadBlockTable();

        $item = $this->getHlBlock($name, $tableName);
        if ($item) {
            return $item;
        }

        $result = $hlBlockTable->add([
            'NAME' => $name,
            'TABLE_NAME' => $tableName,
        ]);
        if ($result->isSuccess()) {
            pre('createHlBlock: success ' . $name);
        } else {
            pre('createHlBlock: error ' . $name);
            pre($result->getErrorMessages());
            throw new \Exception('createHlBlock: error ' . $name);
        }
        $item = $this->getHlBlock($name, $tableName);
        if ($item) {
            return $item;
        }

        return [];
    }

    public function setSaleOrderProperty($properties)
    {
        $saleProperty = new \CSaleOrderProps();
        foreach ($properties as $propertySettings) {
            if (!$propertySettings['CODE'] || !$propertySettings['PERSON_TYPE_ID'] || !$propertySettings['NAME']) {
                pre('setSaleOrderProperty - empty CODE, PERSON_TYPE_ID or NAME.');
                continue;
            }

            $filter = [
                'CODE' => $propertySettings['CODE'],
                'PERSON_TYPE_ID' => $propertySettings['PERSON_TYPE_ID'],
            ];
            $property = $this->getSaleProperty($filter);
            if (!$property) {
                $filter = [
                    'NAME' => $propertySettings['NAME'],
                    'PERSON_TYPE_ID' => $propertySettings['PERSON_TYPE_ID'],
                ];
                $property = $this->getSaleProperty($filter);
            }
            if (!$property) {
                $result = $saleProperty->add($propertySettings);
                pre('setSaleOrderProperty - $result add');
                pre($result);
                $property = $this->getSaleProperty($filter);
            }
            if (!$property) {
                pre('setSaleOrderProperty - property not found ' . $propertySettings['CODE']);
                continue;
            }
            $result = $saleProperty->Update($property['ID'], $propertySettings);
            pre('setSaleOrderProperty - $result Update');
            pre($result);
        }
    }
}
