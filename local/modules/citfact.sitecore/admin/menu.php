<?php

/*
 * This file is part of the Studio Fact package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Bitrix\Main\Localization\Loc;
use Citfact\SiteCore\User\UserRepository;

Loc::loadMessages(__FILE__);

$menuList = [
	[
        'parent_menu' => 'global_menu_sitecore',
        'sort' => 10,
		'text' => 'Настройки',
		'title' => 'Настройки',
		'url' => 'citfact_sitecore_homepage.php',
		'icon' => 'sys_menu_icon',
        'items' => []
	],
    [
        'parent_menu' => 'global_menu_sitecore',
        'sort' => 20,
        'text' => 'Загрузка прайслиста',
        'title' => 'Загрузка прайслиста',
        'url' => 'citfact.sitecore.upload_pricelist.php',
        'icon' => 'sys_menu_icon',
        'items' => []
    ]
];

return $menuList ?? [];
