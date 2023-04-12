<?
$bitrixpath = $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/citfact.sitecore/admin/upload_pricelist.php";
$localpath = $_SERVER["DOCUMENT_ROOT"]."/local/modules/citfact.sitecore/admin/upload_pricelist.php";
if (file_exists($bitrixpath)) {
    require($bitrixpath);
}
else if (file_exists($localpath)){
    require ($localpath);
}