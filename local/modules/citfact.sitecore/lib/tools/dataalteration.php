<?php

namespace Citfact\SiteCore\Tools;

class DataAlteration
{
    /**
     * @param int $number
     * @param array $titles
     *
     * @param bool $onlyTitles
     * @return string word
     */
    public function declension($number, $titles, $onlyTitles = false)
    {
        $cases = array(2, 0, 1, 1, 1, 2);
        $pref = $number . ' ';
        if ($onlyTitles === true) {
            $pref = '';
        }
        return $pref . $titles[($number % 100 > 4 && $number % 100 < 20) ? 2 : $cases[min($number % 10, 5)]];
    }

    /**
     * @param string $date_from
     * @param string $date_to
     * @return array
     */
    public function dateDifference($date_from, $date_to)
    {
        $date_from = new \DateTime($date_from);
        $date_to = new \DateTime($date_to);
        $interval = $date_from->diff($date_to);
        $arReturn = array(
            'days' => $interval->days,
            'm' => $interval->m,
            'd' => $interval->d,
            'invert' => $interval->invert,
        );

        return $arReturn;
    }

    /**
     * Приводим номер телефона к 11 цифрам
     * @param $phone
     * @param bool $addPrefix
     * @return string
     */
    public function clearPhone($phone, $addPrefix = true)
    {
        if (!$phone) {
            return '';
        }
        $phone = htmlspecialcharsbx(trim($phone));

        $phone = preg_replace('~\D~', '', $phone);
        $phone = preg_replace('/[^0-9]/', '', $phone);

        if ($addPrefix === true) {
            if (strlen($phone) == 10) {
                $phone = '7' . $phone;
            }
        }

        if (strlen($phone) == 11 && substr($phone, 0, 1) == 8) {
            $phone = substr_replace($phone, "7", 0, 1);
        }

        return $phone;
    }

    public function stylePhone($phone)
    {
        if (preg_match('/^(\d{3})(\d{3})(\d{4})$/', $phone, $matches)) {
            $result = '+7 (' . $matches[1] . ') ' . $matches[2] . '-' . $matches[3];
            return $result;
        } elseif (preg_match('/^(\d{1})(\d{3})(\d{3})(\d{4})$/', $phone, $matches)) {
            $result = '+' . $matches[1] . ' (' . $matches[2] . ') ' . $matches[3] . '-' . $matches[4];
            return $result;
        }

        return $phone;
    }

    public function requestSpecialChars($requestData)
    {
        foreach ($requestData as $code => &$value) {
            if (is_array($value)) {
                continue;
            }
            $value = htmlspecialcharsbx(trim(strip_tags($value)));
            if (strpos($code, 'INN') !== false || strpos($code, 'KPP') !== false) {
                $value = str_replace('_', '', $value);
            }
        }
        unset($value);
        return $requestData;
    }


    /**
     * Поиск в строке элементов из переданного массива
     * @param $string - где ищем
     * @param $needles - что ищем
     * @param false $getKey - возвращать не позицию в строке, а ключ из переданного массива
     * @return false|int|string
     */
    public function strposArray($string, $needles, bool $getKey = false){
        if ( is_array($needles) ) {
            foreach ($needles as $key => $str) {
                if ( is_array($str) ) {
                    $pos = $this->strposArray($string, $str);
                } else {
                    $pos = mb_strpos($string, $str);
                }
                if ($pos !== false) {
                    return ($getKey ? $key : $pos);
                }
            }
        } else {
            return mb_strpos($string, $needles);
        }

        return false;
    }

    /**
     * Превращает строку в число с учетом точек и запятых в строке
     * @param $val
     * @return float
     */
    public static function floatValue($val)
    {
        $val = str_replace(",", ".", $val);
        $val = preg_replace('/\.(?=.*\.)/', '', $val);
        return floatval($val);
    }

    /**
     * Валидация емейла
     * @param $email string
     * @return bool
     */
    public function validateEmail(string $email = ''): bool
    {
        $rexExp = '#^([\w]+\.?)+(?<!\.)@(?!\.)[a-zа-я0-9ё\.-]+\.?[a-zа-яё]{2,}$#ui';
        return (bool)preg_match($rexExp, $email);
    }


    /**
     * Переводим в верхний регистр первый символ строки
     * @param $str
     * @param $encoding
     * @return string
     */
    public static function uppercaseFirstLetter($str, $encoding = 'UTF-8')
    {
        return mb_strtoupper(mb_substr($str, 0, 1, $encoding), $encoding)
            . mb_substr($str, 1, null, $encoding);
    }
}