<?php

use Citfact\SiteCore\Constants;

setlocale(LC_ALL, 'ru_RU.utf8');
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

$module_id = "citfact.sitecore";

CModule::IncludeModule("iblock");
CModule::IncludeModule($module_id);
IncludeModuleLangFile(__FILE__);

CJSCore::Init(array("jquery"));?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$arFirstTabOptions = array(
    array(Constants::RECAPTCHA_PUBLIC_KEY, GetMessage("SITECORE_RECAPTCHA_PUBLIC_KEY"), "", array("text", "50")),
    array(Constants::RECAPTCHA_PRIVATE_KEY, GetMessage("SITECORE_RECAPTCHA_PRIVATE_KEY"), "", array("text", "50")),
    array(Constants::YANDEX_KEY, GetMessage("SITECORE_YANDEX_KEY"), "", array("text", "50")),
    array(Constants::SMS_SERVICE_LOGIN, GetMessage("SITECORE_SMS_SERVICE_LOGIN"), "", array("text", "50")),
    array(Constants::SMS_SERVICE_PASSWORD, GetMessage("SITECORE_SMS_SERVICE_PASSWORD"), "", array("text", "50")),
    array(Constants::DIGINETICA_KEY, GetMessage("DIGINETICA_KEY"), "", array("text", "50")),
);

$arSecondTabOptions = array(
    // array(Constants::DISCOUNT_IMPORT_LIMIT, GetMessage("SITECORE_DISCOUNT_IMPORT_LIMIT"), "", array("text", "20")),
);

if($_REQUEST['param'] && check_bitrix_sessid()) {
	if ($Update) {
        foreach($arFirstTabOptions as $arOption)
        {
            $name = $arOption[0];
            $val = $_REQUEST['param'][$name];

            if($arOption[2][0] === "checkbox" && $val !== "Y") {
                $val = "N";
            }

            COption::SetOptionString($module_id, $name, $val, $arOption[1]);
        }
    } elseif ($_REQUEST['UPDATE_LIMIT']) {
        foreach($arSecondTabOptions as $arOption)
        {
            $name = $arOption[0];
            $val = $_REQUEST['param'][$name];

            if($arOption[2][0] === "checkbox" && $val !== "Y") {
                $val = "N";
            }

            COption::SetOptionString($module_id, $name, $val, $arOption[1]);
        }
    }
}

$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("SITECORE_HOMEPAGE_TAB1_HEAD"), "TITLE" => GetMessage("SITECORE_HOMEPAGE_TAB1_HEAD")),
	array("DIV" => "edit2", "TAB" => GetMessage("SITECORE_HOMEPAGE_TAB2_HEAD"), "TITLE" => GetMessage("SITECORE_HOMEPAGE_TAB2_HEAD")),
	array("DIV" => "edit3", "TAB" => GetMessage("SITECORE_HOMEPAGE_TAB3_HEAD"), "TITLE" => GetMessage("SITECORE_HOMEPAGE_TAB3_HEAD")),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs);
?>

<?if (!empty($arErrors)):?>
	<div class="adm-info-message-wrap adm-info-message-red">
		<div class="adm-info-message">
			<div class="adm-info-message-title">Ошибка</div>
			<?foreach($arErrors as $error):?>
				<?=$error?><br />
			<?endforeach?>
			<div class="adm-info-message-icon"></div>
		</div>
	</div>
<?endif?>

<?$tabControl->Begin();?>
<?$tabControl->BeginNextTab(['className' => 'table-options']);?>
    <form method="post" action="<?= $APPLICATION->GetCurPage()?>" enctype="multipart/form-data" name="post_form" id="post_form">

        <?/*<tr class="heading">
            <td colspan="2"><b>Системные настройки</b></td>
        </tr>*/?>

        <?= bitrix_sessid_post();
        foreach($arFirstTabOptions as $arOption):
            $val = COption::GetOptionString($module_id, $arOption[0], $arOption[2]);
            $type = $arOption[3];?>
            <tr>
                <td <?= ($type[0]=="textarea") ? 'class="adm-detail-valign-top"' : '' ?>>
                    <label for="<?= htmlspecialcharsbx($arOption[0])?>"><?= $arOption[1]?>:</label>
                </td>
                <td>
                    <?if($type[0]=="checkbox"):?>
                        <input type="checkbox" id="<?= htmlspecialcharsbx($arOption[0])?>" name="param[<?= htmlspecialcharsbx($arOption[0])?>]" value="Y"<?= ($val === "Y") ? " checked" : ''?>>
                    <?elseif($type[0]=="text"):?>
                        <input type="text" size="<?= $type[1]?>" maxlength="255" value="<?= htmlspecialcharsbx($val)?>" name="param[<?= htmlspecialcharsbx($arOption[0])?>]">
                    <?elseif($type[0]=="textarea"):?>
                        <textarea rows="<?= $type[1]?>" cols="<?= $type[2]?>" name="param[<?= htmlspecialcharsbx($arOption[0])?>]"><?= htmlspecialcharsbx($val)?></textarea>
                    <?elseif($type[0]=="select"):?>
                        <select name="param[<?= htmlspecialcharsbx($arOption[0])?>]" id="<?= htmlspecialcharsbx($arOption[0])?>">
                            <?
                            foreach($arOption[2] as $key => $v)
                            {
                                ?>
                                <option value="<?=$key?>" <?= ($key == $val) ? " selected" : ''?>
                                ><?=htmlspecialcharsex($v)?></option><?
                            }
                            ?>
                        </select>
                    <?endif?>
                </td>
            </tr>
        <?endforeach?>
        <input class="adm-btn" type="submit" name="Update" value="<?=GetMessage("SITECORE_HOMEPAGE_SUBMIT"); ?>" title="<?=GetMessage("SITECORE_HOMEPAGE_SUBMIT"); ?>">
    </form>
<?$tabControl->BeginNextTab();?>
    <form method="post" action="<?= $APPLICATION->GetCurPage() . '?tabControl_active_tab=edit2'?>" enctype="multipart/form-data" name="post_form_2" id="post_form_2">
        <?= bitrix_sessid_post();
        foreach($arSecondTabOptions as $arOption):
            $val = COption::GetOptionString($module_id, $arOption[0], $arOption[2]);
            $type = $arOption[3];?>
            <tr>
                <td <?= ($type[0]=="textarea") ? 'class="adm-detail-valign-top"' : '' ?>>
                    <label for="<?= htmlspecialcharsbx($arOption[0])?>"><?= $arOption[1]?>:</label>
                <td>
                    <?if($type[0]=="checkbox"):?>
                        <input type="checkbox" id="<?= htmlspecialcharsbx($arOption[0])?>" name="param[<?= htmlspecialcharsbx($arOption[0])?>]" value="Y"<?= ($val === "Y") ? " checked" : ''?>>
                    <?elseif($type[0]=="text"):?>
                        <input type="text" size="<?= $type[1]?>" maxlength="255" value="<?= htmlspecialcharsbx($val)?>" name="param[<?= htmlspecialcharsbx($arOption[0])?>]">
                    <?elseif($type[0]=="textarea"):?>
                        <textarea rows="<?= $type[1]?>" cols="<?= $type[2]?>" name="param[<?= htmlspecialcharsbx($arOption[0])?>]"><?= htmlspecialcharsbx($val)?></textarea>
                    <?elseif($type[0]=="select"):?>
                        <select name="param[<?= htmlspecialcharsbx($arOption[0])?>]" id="<?= htmlspecialcharsbx($arOption[0])?>">
                            <?
                            foreach($arOption[2] as $key => $v)
                            {
                                ?>
                                <option value="<?=$key?>" <?= ($key == $val) ? " selected" : ''?>
                                ><?=htmlspecialcharsex($v)?></option><?
                            }
                            ?>
                        </select>
                    <?endif?>
                </td>
            </tr>
        <?endforeach?>
        <input class="adm-btn" type="submit" name="UPDATE_LIMIT" value="<?=GetMessage("SITECORE_HOMEPAGE_SUBMIT"); ?>" title="<?=GetMessage("SITECORE_HOMEPAGE_SUBMIT"); ?>">
    </form>

<?$tabControl->BeginNextTab();?>


<?$tabControl->Buttons();?>
<?$tabControl->End()?>


<style>
    .table-options td.adm-detail-content-cell-l{
        max-width: 100px;
    }
</style>


<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
