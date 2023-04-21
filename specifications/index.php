<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Citfact\SiteCore\ProductSpecifications\Update;

Loc::loadMessages(__FILE__);
Loader::includeModule("citfact.sitecore");

$APPLICATION->SetTitle(Loc::getMessage('TITLE'));
$APPLICATION->SetPageProperty("TITLE", Loc::getMessage('TITLE'));

?>
    <link href="./specifications.min.css" rel="stylesheet">
    <script src="./specifications.min.js" async></script>
    <div class="loader-wrap js-loader-wrap active">
        <div class="loader">
            <div class="loader-container">
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
            </div>
        </div>
    </div>
    <div class="about default-container">
        <div class="about-wrapper">
            <?php
            $APPLICATION->IncludeComponent(
                'citfact:specifications',
                'specification.wallpaper',
                [
                    'HL_BLOCK' => Update::getHigloadBlock(),
                    'PAGE_SIZE' => 15,
                    'PAGE' => $_GET['page'] ?: 1,
                    'IS_MOBILE' => isset($_GET['isMobile']),
                ]
            );
            ?>
        </div>
    </div>
<?php

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
