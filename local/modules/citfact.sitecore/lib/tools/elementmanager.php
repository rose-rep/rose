<?php

namespace Citfact\SiteCore\Tools;

class ElementManager
{
    public function stylePhone($phone, $for1c = false)
    {
        if ($for1c) {
            if (preg_match('/^(\d{3})(\d{3})(\d{4})$/', $phone, $matches)) {
                $result = '+7 (' . $matches[1] . ') ' . $matches[2] . $matches[3];
                return $result;
            } elseif (preg_match('/^(\d{1})(\d{3})(\d{3})(\d{4})$/', $phone, $matches)) {
                $result = '+' . $matches[1] . ' (' . $matches[2] . ') ' . $matches[3] . $matches[4];
                return $result;
            }
        } else {
            if (preg_match('/^(\d{3})(\d{3})(\d{4})$/', $phone, $matches)) {
                $result = '+7 (' . $matches[1] . ') ' . $matches[2] . '-' . $matches[3];
                return $result;
            } elseif (preg_match('/^(\d{1})(\d{3})(\d{3})(\d{4})$/', $phone, $matches)) {
                $result = '+' . $matches[1] . ' (' . $matches[2] . ') ' . $matches[3] . '-' . $matches[4];
                return $result;
            }
        }

        return $phone;
    }

    /**
     * @param $ipropValues
     * @param $name
     * @param string $type
     */
    public static function setIpropValues($ipropValues, $name, $type = 'ELEMENT')
    {
        global $APPLICATION;
        if ($ipropValues[$type . '_PAGE_TITLE'] != '') {
            $APPLICATION->SetTitle($ipropValues[$type . '_PAGE_TITLE']);
        } elseif ($name) {
            $APPLICATION->SetTitle($name);
        }

        if ($ipropValues[$type . '_META_TITLE']) {
            $APPLICATION->SetPageProperty('title', $ipropValues[$type . '_META_TITLE']);
        } elseif ($name) {
            $APPLICATION->SetPageProperty('title', $name);
        }

        if ($ipropValues[$type . '_META_KEYWORDS']) {
            $APPLICATION->SetPageProperty('title', $ipropValues[$type . '_META_KEYWORDS']);
        }

        if ($ipropValues[$type . '_META_KEYWORDS']) {
            $APPLICATION->SetPageProperty('title', $ipropValues[$type . '_META_KEYWORDS']);
        }

        if ($ipropValues[$type . '_META_DESCRIPTION']) {
            $APPLICATION->SetPageProperty('title', $ipropValues[$type . '_META_DESCRIPTION']);
        }
    }
}