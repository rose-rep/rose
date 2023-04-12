<?
$bitrixpath = $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/citfact.sitecore/admin/sitecore_homepage.php";
$localpath = $_SERVER["DOCUMENT_ROOT"]."/local/modules/citfact.sitecore/admin/sitecore_homepage.php";
if (file_exists($bitrixpath)) {
    require($bitrixpath);
}
else if (file_exists($localpath)){
    require ($localpath);
}
?>