<?

namespace Citfact\SiteCore\Tools\Migration;


use Bitrix\Main\UserFieldTable;
use Citfact\SiteCore\Tools\User\UserFieldEnumTable;

class UserFieldMigration extends BaseMigration
{
    public function setUserFields($dataItems)
    {
        foreach ($dataItems as $data) {
            if (!$data['FIELD_NAME'] || !$data['ENTITY_ID']) {
                pre('updateUserField: error - empty FIELD_NAME or ENTITY_ID.');
                return;
            }
            $userField = new UserFieldTable();
            $res = $userField->getList([
                'filter' => [
                    'FIELD_NAME' => $data['FIELD_NAME'],
                    'ENTITY_ID' => $data['ENTITY_ID'],
                ]
            ]);
            $item = $res->fetch();

            if ($item) {
                $this->updateUserField($item['ID'], $data);
                $data['USER_FIELD_ID'] = $item['ID'];
            } else {
                $id = $this->addUserField($data);
                $data['USER_FIELD_ID'] = $id;
            }
            if ($data['ENUM']) {
                $this->setUserFieldEnum($data);
            }
        }
    }

    public function updateUserField($id, $data)
    {
        $userTypeEntity = new \CUserTypeEntity();
        $result = $userTypeEntity->update($id, $data);
        pre('updateUserField: ' . $data['FIELD_NAME'] . ' result - ' . $result);
    }

    public function addUserField($data)
    {
        $userTypeEntity = new \CUserTypeEntity();
        $result = $userTypeEntity->Add($data);
        pre('addUserField: ' . $data['FIELD_NAME'] . ' result - ' . $result);
    }

    public function setUserFieldEnum($data)
    {
        if (!$data['USER_FIELD_ID']) {
            return;
        }
        $userFieldEnum = new UserFieldEnumTable();
        $res = $userFieldEnum->getList([
            'filter' => ['=USER_FIELD_ID' => $data['USER_FIELD_ID']]
        ]);
        $items = [];
        while ($item = $res->Fetch()) {
            if (!$data['ENUM'][$item['XML_ID']]) {
                pre('setUserFieldEnum: delete - ' . $item['ID']);
                $userFieldEnum->delete($item['ID']);
                continue;
            }
            $items[$item['XML_ID']] = $item;
        }
        $i = 0;
        foreach ($data['ENUM'] as $xmlId => $value) {
            $i++;
            if ($items[$xmlId]) {
                pre('setUserFieldEnum: update - ' . $items[$xmlId]['ID']);
                $userFieldEnum->update($items[$xmlId]['ID'], [
                    'SORT' => $i,
                    'VALUE' => $value,
                    'USER_FIELD_ID' => $data['USER_FIELD_ID'],
                ]);
            } else {
                $result = $userFieldEnum->add([
                    'SORT' => $i,
                    'VALUE' => $value,
                    'XML_ID' => $xmlId,
                    'USER_FIELD_ID' => $data['USER_FIELD_ID'],
                ]);
                pre('setUserFieldEnum: add - ' . $result->getId());
            }
        }
    }
}