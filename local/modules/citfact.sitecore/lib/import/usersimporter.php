<?php

namespace Citfact\Sitecore\Import;

//use \Bitrix\Main\Loader;
use Bitrix\Main\Diag\Debug;
use Bitrix\Main\Entity\Query as EntityQuery;
use Bitrix\Main\UserTable;
use Citfact\Tools\Logger\Logger;


class UsersImporter
{
    private $logger;
    private $filePath;
    private $arUsers = [];
    private $usersMap = [];
    private $countUpdated = 0;
    private $countCreated = 0;


    public function __construct($filePath)
    {
        if (!file_exists($filePath) || filesize($filePath) == 0){
            throw new \Exception('File is not exists or file is empty');
        }

        $this->filePath = $filePath;

        $this->logger = new Logger();
        $this->logger->setLogPath('/local/var/logs');
        $this->logger->setLogName("import_users_".date('Y-m-d'));
    }


    public function run(){
        $this->logger->addToLog('Запущен импорт пользователей '.date('Y-m-d H-i-s'));
        Debug::startTimeLabel('import_users');

        $this->readFile();
        if (!empty($this->arUsers)){
            $this->updateUsers();
        }

        Debug::endTimeLabel('import_users');
        $arLabels = Debug::getTimeLabels();
        $this->logger->addToLog('Импорт завершен за '.$arLabels['import_users']['time']);

        // TODO: добавление ошибок в лог + вывод статуса failure и ошибок
        $this->logger->addToStatus("success\n");
        $this->logger->addToStatus('Обновлено пользователей: ' . $this->countUpdated . "\n");
        $this->logger->addToStatus('Создано пользователей: ' . $this->countCreated . "\n");
        $this->logger->showStatus();
    }


    /**
     * @throws \Exception
     * @internal param $filePath
     */
    private function readFile()
    {
        $reader = new \XMLReader();
        $filePath = $this->filePath;

        if (!$reader->open($filePath)) {
            $this->logger->addToLog('Ошибка открытия XML');
            throw new \Exception('Ошибка открытия XML '.$filePath);
        }

        // move to the <w:body> node
        //while ($reader->read() && $reader->name !== 'w:body');

        $arUser = array();
        while ($reader->read()) {
            //var_dump($reader->name);
            //var_dump($reader->nodeType);
            //var_dump($reader->value);

            // <USER>
            if ($reader->nodeType == \XMLREADER::ELEMENT && $reader->name == 'USER'){
                $arUser = array();
            }

            // Поля пользователя
            if ($reader->nodeType == \XMLREADER::ELEMENT && $reader->name != 'USER'){
                $nodeName = $reader->name;
            }
            if ($reader->nodeType == \XMLREADER::TEXT){
                $arUser[$nodeName] = $reader->value;
            }

            // </USER>
            if ($reader->nodeType == \XMLREADER::END_ELEMENT && $reader->name == 'USER'){
                $this->arUsers[] = $arUser;
            }
        }

        $reader->close();
    }


    private function updateUsers()
    {
        $this->setUsersMap();

        $user = new \CUser;
        foreach ($this->arUsers as $arUser) {
            // Если нашли телефон в usersMap, то обновляем пользователя
            // Если не нашли, то создаем нового пользователя
            if (isset($this->usersMap[$arUser['PERSONAL_PHONE']])){
                if ( !$user->Update(
                    $this->usersMap[$arUser['PERSONAL_PHONE']],
                    array(
                        'NAME' => $arUser['NAME'],
                        'EMAIL' => $arUser['EMAIL'],
                        'PERSONAL_PHONE' => $arUser['PERSONAL_PHONE'],
                        'UF_ADDITIONAL_PHONE' => $arUser['ADDITIONAL_PHONE'],
                        'UF_NEED_EXPORT' => 'N'
                        )
                    )
                ){
                    $this->logger->addToLog('Ошибка обновления пользователя: ' . $user->LAST_ERROR);
                }
                else{
                    $this->logger->addToLog('Обновлен пользователь с ID ' . $this->usersMap[$arUser['PERSONAL_PHONE']]);
                    $this->countUpdated++;
                }
            }
            else{
                $password = strtolower(randString(8));
                $arFields = Array(
                    "NAME"              => $arUser['NAME'],
                    "EMAIL"             => $arUser['EMAIL'],
                    "LOGIN"             => $arUser['PERSONAL_PHONE'],
                    "ACTIVE"            => "Y",
                    "PASSWORD"          => $password,
                    "CONFIRM_PASSWORD"  => $password,
                    "PERSONAL_PHONE"    => $arUser['PERSONAL_PHONE'],
                    'UF_ADDITIONAL_PHONE' => $arUser['ADDITIONAL_PHONE'],
                    'UF_NEED_EXPORT' => 'N',
                );

                $ID = $user->Add($arFields);
                if (intval($ID) > 0) {
                    $this->logger->addToLog('Создан пользователь с ID ' . $ID);
                    $this->countCreated++;
                }
                else {
                    $this->logger->addToLog('Ошибка создания пользователя: ' . $user->LAST_ERROR);
                }
            }

        }
    }


    private function setUsersMap()
    {
        $arPhones = [];
        foreach ($this->arUsers as $arUser){
            $arPhones[] = $arUser['PERSONAL_PHONE'];
        }

        if (!empty($arPhones)){
            $tableEntity = UserTable::getEntity();
            $query = new EntityQuery($tableEntity);
            $filter = ['PERSONAL_PHONE' => $arPhones];
            $query
                ->setSelect(array('ID', 'PERSONAL_PHONE'))
                ->setFilter($filter);
            $result = $query->exec();
            while ($row = $result->fetch()) {
                $this->usersMap[$row['PERSONAL_PHONE']] = $row['ID'];
            }
        }
    }
}