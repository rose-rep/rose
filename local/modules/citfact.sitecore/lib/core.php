<?php

namespace Citfact\SiteCore;

use CIBlockElement;
use Citfact\SiteCore\Tools\HLBlock;
use Exception;


class  Core
{
    const CONSTANTS_FILE_PATH = '/local/php_interface/constants.dat';
    const IBLOCK_CODE_CATALOG = 'product';
    const IBLOCK_CODE_ACTIONS = 'actions';
    const IBLOCK_CODE_ARTICLES = 'articles';
    const IBLOCK_CODE_NEWS = 'news';
    const IBLOCK_CODE_MENU_LEFT = 'menu_left';
    const IBLOCK_CODE_MENU_ACCOUNT = 'menu_account';
    const IBLOCK_CODE_PROJECTS = 'projects';
    const IBLOCK_CODE_AFFILIATES = 'affiliate';

    const IBLOCK_CODE_CATALOG_ROSE_WALLPAPER = 'catalog__rose_wallpaper';
    const PRICE_MIN_RETAIL_ID = 12;

    const CURRENCY_CODE = "RUB";

    private $curDir;
    private $curPage;


    private $constants;

    public static array $productsIdsFiltered = [];
    public static bool $isBasket = false;
    public static bool $isBasketFilter = false;
    public static array $productsForRefresh = [];
    public static int $basketArticlesCount = 0;
    public static int $basketCounter = 0;

    /**
     * @var Core The reference to *Singleton* instance of this class
     */
    protected static $instance;

    /**
     * Returns the *Core* instance of this class.
     *
     * @return Core The *Core* instance.
     */
    public static function getInstance()
    {
        if (null === static::$instance) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * Protected constructor to prevent creating a new instance of the
     * *Core* via the `new` operator from outside of this class.
     */
    protected function __construct()
    {
        global $APPLICATION;
        $this->curDir = $APPLICATION->GetCurDir();
        $this->curPage = $APPLICATION->GetCurPage();

        $fileData = file_get_contents($_SERVER['DOCUMENT_ROOT'] . self::CONSTANTS_FILE_PATH);
        $this->constants = json_decode($fileData, true);
    }

    /**
     * Код филиала по его id
     *
     * @param $affiliateId
     * @return mixed
     * @throws Exception
     */
    public function getBranchCode($affiliateId)
    {
        if (!$affiliateId) {
            throw new Exception('Empty affiliate id.');
        }

        if ($this->constants['AFFILIATE_' . $affiliateId]) {
            return $this->constants['AFFILIATE' . $affiliateId];
        }

        $affiliateCode = CIBlockElement::GetList(
            [],
            [
                'IBLOCK_ID' => $this->getIblockId(Core::IBLOCK_CODE_AFFILIATES),
                'ID' => $affiliateId
            ],
            false, false,
            ['ID', 'IBLOCK_ID', 'PROPERTY_AFFILIATE_CODE']
        )->Fetch()['PROPERTY_AFFILIATE_CODE_VALUE'];

        $this->constants['AFFILIATE' . $affiliateId] = $affiliateCode;
        ksort($this->constants);

        file_put_contents(
            $_SERVER['DOCUMENT_ROOT'] . self::CONSTANTS_FILE_PATH,
            json_encode($this->constants)
        );

        return $affiliateCode;
    }

    /**
     * @param $iblockCode
     * @return string
     * @throws Exception
     */
    public function getIblockId($iblockCode)
    {
        if (!$iblockCode) {
            throw new Exception('Empty iblock code.');
        }
        if ($this->constants['IBLOCK_' . $iblockCode]) {
            return $this->constants['IBLOCK_' . $iblockCode];
        }

        $iblock = new \CIBlock();
        $res = $iblock->GetList([], ['CODE' => $iblockCode]);
        $item = $res->Fetch();
        if (!$item['ID']) {
            throw new Exception('Iblock with code ' . $iblockCode . ' not found.');
        }
        $this->constants['IBLOCK_' . $iblockCode] = $item['ID'];
        ksort($this->constants);

        file_put_contents(
            $_SERVER['DOCUMENT_ROOT'] . self::CONSTANTS_FILE_PATH,
            json_encode($this->constants)
        );

        return $item['ID'];
    }

    /**
     * @param $hlBlockCode
     * @return string
     * @throws Exception
     */
    public function getHlBlockId($hlBlockCode)
    {
        if (!$hlBlockCode) {
            throw new Exception('Empty hlBlock code.');
        }
        if ($this->constants['HL_BLOCK_' . $hlBlockCode]) {
            return $this->constants['HL_BLOCK_' . $hlBlockCode];
        }

        $hlBlock = new HLBlock();
        $hlData = $hlBlock->getHlDataByName($hlBlockCode);

        if (!$hlData['ID']) {
            throw new Exception('HlBlock with code ' . $hlBlockCode . ' not found.');
        }
        $this->constants['HL_BLOCK_' . $hlBlockCode] = $hlData['ID'];
        ksort($this->constants);

        file_put_contents(
            $_SERVER['DOCUMENT_ROOT'] . self::CONSTANTS_FILE_PATH,
            json_encode($this->constants)
        );

        return $hlData['ID'];
    }

    /**
     * @return string
     */
    public function getCurDir()
    {
        return $this->curDir;
    }


    /**
     * @return string
     */
    public function getCurPage()
    {
        return $this->curPage;
    }


    /**
     * Private clone method to prevent cloning of the instance of the
     * *Core* instance.
     *
     * @return void
     */
    private function __clone()
    {
    }


    /**
     * Private unserialize method to prevent unserializing of the *Core*
     * instance.
     *
     * @return void
     */
    private function __wakeup()
    {
    }
}
