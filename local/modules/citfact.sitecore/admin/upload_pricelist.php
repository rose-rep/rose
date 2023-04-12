<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");

setlocale(LC_ALL, "ru_RU.utf8");

global $USER, $APPLICATION;

$moduleId = "citfact.sitecore";

if (!$USER->IsAdmin()) {
    $APPLICATION->AuthForm("Доступ запрещен");
}

CModule::IncludeModule("iblock");
CModule::IncludeModule($moduleId);

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");

use Citfact\SiteCore\ProductSpecifications\Update;

$APPLICATION->SetTitle("Технические характеристики продукции - Обновление");

$uploadComplete = false;

/** Перечень полей, которые могут быть переданы в POST */
$arPostFields = [
    "THP_WALLPAPERS" => "thp_wallpapers",
];

foreach ($arPostFields as $field => $variable) {
    if (!empty($_FILES[$field]['name']) && check_bitrix_sessid()) {
        if ($field == "THP_WALLPAPERS") {
            Update::wallpapers($_FILES[$field]['tmp_name']);

            unlink($_FILES[$field]['tmp_name']);

            $uploadComplete = true;
        }
    }
}

$aTabs = [
    [
        "DIV" => "update",
        "TAB" => "Обновить ТХП",
        "TITLE" => $uploadComplete ? "Обновление успешно выполнено" : "Обновить ТХП"
    ]
];

$tabControl = new CAdminTabControl("tabControl", $aTabs);

$tabControl->Begin();
?>
    <form method="post" action="<?= $APPLICATION->GetCurPage(); ?>" enctype="multipart/form-data" action="<?= $_SERVER ['PHP_SELF']; ?>" name="post_form" id="post_form">

        <?php $tabControl->BeginNextTab(); ?>
        <?= bitrix_sessid_post(); ?>

        <table class="adm-detail-content-table edit-table">
            <tbody>
            <tr>
                <td width="40%" class="adm-detail-content-cell-l" style="padding-top:10px; vertical-align:top;">
                    Загрузите файл ТХП - Обои
                </td>
                <td width="60%" class="adm-detail-content-cell-r">
                    <div style="margin-bottom:6px;">
                        <input type="file" style="width:96%;" name="THP_WALLPAPERS">
                    </div>
                </td>
            </tr>
            </tbody>
            <?php $tabControl->Buttons(); ?>
            <input class="adm-btn" type="submit" name="upload_thp" value="Обновить" title="Обновить">
        </table>
    </form>
<?php
$tabControl->End();

require($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/epilog_admin.php");