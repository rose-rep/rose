<?php
//ID инфоблоков нужно получать с помощью метода $core->getIblockId($core::IBLOCK_CODE_CATALOG), добавлять в constants.php нет необходимости.
namespace Citfact\SiteCore;

/**
 * Класс с константами модуля
 */
class Constants {

    /** @var string открытый ключ рекапчи */
    const RECAPTCHA_PUBLIC_KEY = "sitecore_recaptcha_public_key";

    /** @var string секретный ключ рекапчи */
    const RECAPTCHA_PRIVATE_KEY = "sitecore_recaptcha_private_key";

    /** @var string ключ доступа к api яндекс карт*/
    const YANDEX_KEY = "sitecore_yandex_key";

    /** @var string логин СМС-сервиса */
    const SMS_SERVICE_LOGIN = "sitecore_sms_service_login";

    /** @var string пароль СМС-сервиса */
    const SMS_SERVICE_PASSWORD = "sitecore_sms_service_password";

    /** @var string Ключ сервиса поиска Diginetica */
    const DIGINETICA_KEY = "diginetica_key";


    /**
     * Константы инфоблоков
     */

    /** @var string Стандартная сортировка*/
    const DEFAULT_SORT_TYPE = ['SORT' => 'ASC'];


    /** @var string Сортировка по началу активности*/
    const DATE_SORT_TYPE = ['ACTIVE_FROM' => 'DESC'];

    /** @var string Стандартная кол-во элементов*/
    const DEFAULT_IBLOCK_LIMIT = 20;
}

