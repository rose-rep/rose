<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Localization\Loc;
use Citfact\SiteCore\Core;

$core = Core::getInstance();
$this->setFrameMode(true);

Loc::loadMessages(__FILE__);

$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
?>

<div class="specifications static-wrap">
    <div class="static-actions">
        <div class="static-actions__top">
            <div class="specifications-links specifications-links--mb-0">
                <a href="<?= $arParams['WALLPAPERS_LINK']; ?>"
                class="specifications-links__i<?= ($arParams['TAB'] == 'WALLPAPERS') ? ' specifications-links__i active' : '' ?>">
                    <?= Loc::getMessage('WALLPAPER'); ?>
                </a>
                <a href="<?= $arParams['HOME_LINK']; ?>"
                class="specifications-links__i<?= ($arParams['TAB'] == 'HOME') ? ' specifications-links__i active' : '' ?>">
                    <?= Loc::getMessage('HOME'); ?>
                </a>
            </div>
            <button class="download-excel download-excel--with-filters" id="download_excel" type="button">
                <span class="download-excel__text">
                    <?= Loc::getMessage('EXPORT'); ?>
                </span>
                <svg class="download-excel__icon">
                    <use href="<?= SITE_TEMPLATE_PATH; ?>/sprite.svg#excel"></use>
                </svg>
            </button>
        </div>
        <div class="static-actions__container">
            <div class="static-actions__filters">
                <div class="static-actions__filters-container">
                <form class="static-actions__filters-form" novalidate name="specification_search_params" action="<?= $APPLICATION->GetCurPage(); ?>"
                          method="get">
                        <label class="select-wrapper js-select-wrapper">
                            <select class="select js-select js-select-mark" autocomplete="off" name="tradeMark">
                                <option value=""></option>
                                <?php foreach ($arResult['TRADE_MARKS'] as $tradeMark) { ?>
                                    <option value="<?= $tradeMark; ?>" <?= $request->get('tradeMark') == $tradeMark ? 'selected' : ''; ?>>
                                        <?= $tradeMark; ?></a>
                                    </option>
                                <?php } ?>
                            </select>
                            <span class="select-name js-select-name">
                                <?= Loc::getMessage('UF_TRADE_MARK'); ?>
                            </span>
                        </label>
                        <label class="select-wrapper js-select-wrapper">
                            <select class="select js-select js-select-mark" autocomplete="off" name="collection">
                                <option value=""></option>
                                <?php foreach ($arResult['COLLECTIONS'] as $collection) { ?>
                                    <option value="<?= $collection; ?>" <?= $request->get('collection') == $collection ? 'selected' : ''; ?>>
                                        <?= $collection; ?>
                                    </option>
                                <?php } ?>
                            </select>
                            <span class="select-name js-select-name">
                                <?= Loc::getMessage('UF_COLLECTION'); ?>
                            </span>
                        </label>
                        <div class="static-actions__filters-buttons">
                            <a href="<?= $APPLICATION->GetCurPage(); ?>"
                               class="button static-actions__filters-clear js-clear-selects">
                                <svg class="static-actions__filters-clear-icon">
                                    <use href="<?= SITE_TEMPLATE_PATH ?>/sprite.svg#close"></use>
                                </svg>
                                <span>
                                    <?= Loc::getMessage('CLEAR'); ?>
                                </span>
                            </a>
                            <button class="button default-button default-button--primary default-button--small">
                                <?= Loc::getMessage('SELECT'); ?>
                            </button>
                        </div>
                    </form>
                    <form class="input static-search" novalidate name="specification_search_form"
                          action="<?= $APPLICATION->GetCurPage(); ?>" method="get">
                        <input type="search" class="input-block static-search__input"
                               name="wallpaperName" placeholder="<?= Loc::getMessage('SEARCH'); ?>"
                               value="<?= $request->get('wallpaperName'); ?>">
                        <button type="submit" class="static-search__btn">
                            <svg>
                                <use href="<?= SITE_TEMPLATE_PATH; ?>/sprite.svg#search"></use>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php if (!empty($arResult['ITEMS'])) { ?>
        <div class="specifications__inner js-table-wrapper">
            <!-- Todo desktop version при ресайзе не будет работать -->
            <div class="specifications-table js-catalog-infinity-scroll" data-user-agent="desktop">
                <div class="specifications-table__row specifications-table__row--head specifications-table__row--sticky">
                    <div class="specifications-table__cell">
                        <?= GetMessage('UF_NAME'); ?>
                    </div>
                    <?php foreach (current($arResult['ITEMS']) as $code => $value) { ?>
                        <?php if ($code != 'UF_NAME') { ?>
                            <div class="specifications-table__cell <?= $code == 'UF_VOLUME' ? 'specifications-table__cell--big' : ''; ?>">
                                <?= Loc::getMessage($code); ?>
                            </div>
                        <?php } ?>
                    <?php } ?>
                    <div class="specifications-table__cell">
                        <?= GetMessage('COATING_MATERIAL'); ?>
                    </div>
                    <div class="specifications-table__cell">
                        <?= GetMessage('BASE'); ?>
                    </div>
                    <div class="specifications-table__cell">
                        <?= GetMessage('RAPPORT'); ?>
                    </div>
                    <div class="specifications-table__cell">
                        <?= GetMessage('DOCKING'); ?>
                    </div>
                    <div class="specifications-table__cell">
                        <?= GetMessage('UF_ARCHIVE'); ?>
                    </div>
                    <div class="specifications-table__cell">
                        <?= GetMessage('PRICE'); ?>
                    </div>
                    <div class="specifications-table__cell">
                        <?= GetMessage('SITE_PRICE'); ?>
                    </div>
                </div>
                <?php if ($arResult['IS_AJAX'] && !$arParams['IS_MOBILE']) $GLOBALS['APPLICATION']->RestartBuffer();
                foreach ($arResult['ITEMS'] as $item) { ?>
                    <div class="specifications-table__row">
                        <div class="specifications-table__cell">
                            <span>
                                <?= Loc::getMessage("UF_NAME"); ?>
                            </span>
                            <?= $item['UF_NAME']; ?>
                        </div>

                        <!-- Todo повторяющийся код -->
                        <?php foreach ($item as $code => $value) { ?>
                            <?php if ($code != 'UF_NAME') { ?>
                                <div class="specifications-table__cell <?= $code == 'UF_VOLUME' ? 'specifications-table__cell--big' : ''; ?>">
                                        <span>
                                            <?= Loc::getMessage($code); ?>
                                        </span>
                                    <?= $value; ?>
                                </div>
                            <?php } ?>
                        <?php } ?>
                        <div class="specifications-table__cell">
                            <span>
                                <?= Loc::getMessage("COATING_MATERIAL"); ?>
                            </span>
                            <?= $arResult['COATING_MATERIAL'][$item['UF_ARTICLE']] ?? '-'; ?>
                        </div>
                        <div class="specifications-table__cell">
                            <span>
                                <?= Loc::getMessage("BASE"); ?>
                            </span>
                            <?= $arResult['BASE'][$item['UF_ARTICLE']] ?? '-'; ?>
                        </div>
                        <div class="specifications-table__cell">
                            <span>
                                <?= Loc::getMessage("RAPPORT"); ?>
                            </span>
                            <?= $arResult['RAPPORT'][$item['UF_ARTICLE']] ?? '-'; ?>
                        </div>
                        <div class="specifications-table__cell">
                            <span>
                                <?= Loc::getMessage("DOCKING"); ?>
                            </span>
                            <?= $arResult['DOCKING'][$item['UF_ARTICLE']] ?? '-'; ?>
                        </div>
                        <div class="specifications-table__cell">
                                <span>
                                    <?= Loc::getMessage("UF_ARCHIVE"); ?>
                                </span>
                            <?= in_array($item['UF_ARTICLE'], $arResult['ARCHIVE'])
                                ? Loc::getMessage("YES") : Loc::getMessage("NO"); ?>
                        </div>
                        <div class="specifications-table__cell">
                                <span>
                                    <?= Loc::getMessage("PRICE"); ?>
                                </span>
                            <?= $arResult['PRICES'][$item['UF_ARTICLE']] ?? '-'; ?>
                        </div>
                        <div class="specifications-table__cell">
                                <span>
                                    <?= Loc::getMessage("SITE_PRICE"); ?>
                                </span>
                            <?= $arResult['SITE_PRICES'][$item['UF_ARTICLE']] ?? '-'; ?>
                        </div>
                    </div>
                <?php }
                if ($arResult['IS_AJAX'] && !$arParams['IS_MOBILE']) die();
                ?>
            </div>

            <!-- Todo mobile version при ресайзе не будет работать -->
            <div class="specifications-table js-catalog-infinity-scroll js-toggle-parent" data-user-agent="mobile">
                <?php if ($arResult['IS_AJAX']) $GLOBALS['APPLICATION']->RestartBuffer();
                foreach ($arResult['ITEMS'] as $item) { ?>
                    <div class="specifications-table__row toggle js-toggle-wrap">
                        <div class="specifications-table__arr toggle__btn js-toggle">
                            <svg>
                                <use href="<?= SITE_TEMPLATE_PATH; ?>/sprite.svg#arrow-down"></use>
                            </svg>
                        </div>
                        <div class="specifications-table__cell">
                            <span>
                                <?= Loc::getMessage("UF_NAME"); ?>
                            </span>
                            <?= $item['UF_NAME']; ?>
                        </div>
                        <div class="specifications-table__collapse toggle__collapse js-toggle-content">

                            <!-- Todo повторяющийся код -->
                            <?php foreach ($item as $code => $value) { ?>
                                <?php if ($code != 'UF_NAME') { ?>
                                    <div class="specifications-table__cell <?= $code == 'UF_VOLUME' ? 'specifications-table__cell--big' : ''; ?>">
                                        <span>
                                            <?= Loc::getMessage($code); ?>
                                        </span>
                                        <?= $value; ?>
                                    </div>
                                <?php } ?>
                            <?php } ?>
                            <div class="specifications-table__cell">
                                <span>
                                    <?= Loc::getMessage("COATING_MATERIAL"); ?>
                                </span>
                                <?= $arResult['COATING_MATERIAL'][$item['UF_ARTICLE']] ?? '-'; ?>
                            </div>
                            <div class="specifications-table__cell">
                                <span>
                                    <?= Loc::getMessage("BASE"); ?>
                                </span>
                                <?= $arResult['BASE'][$item['UF_ARTICLE']] ?? '-'; ?>
                            </div>
                            <div class="specifications-table__cell">
                                <span>
                                    <?= Loc::getMessage("RAPPORT"); ?>
                                </span>
                                <?= $arResult['RAPPORT'][$item['UF_ARTICLE']] ?? '-'; ?>
                            </div>
                            <div class="specifications-table__cell">
                                <span>
                                    <?= Loc::getMessage("DOCKING"); ?>
                                </span>
                                <?= $arResult['DOCKING'][$item['UF_ARTICLE']] ?? '-'; ?>
                            </div>

                            <div class="specifications-table__cell">
                                <span>
                                    <?= Loc::getMessage("UF_ARCHIVE"); ?>
                                </span>
                                <?= in_array($item['UF_ARTICLE'], $arResult['ARCHIVE'])
                                    ? Loc::getMessage("YES") : Loc::getMessage("NO"); ?>
                            </div>
                            <div class="specifications-table__cell">
                                <span>
                                    <?= Loc::getMessage("PRICE"); ?>
                                </span>
                                <?= $arResult['PRICES'][$item['UF_ARTICLE']] ?? '-'; ?>
                            </div>
                            <div class="specifications-table__cell">
                                <span>
                                    <?= Loc::getMessage("SITE_PRICE"); ?>
                                </span>
                                <?= $arResult['SITE_PRICES'][$item['UF_ARTICLE']] ?? '-'; ?>
                            </div>
                        </div>
                    </div>
                <?php }
                if ($arResult['IS_AJAX']) die();
                ?>
            </div>

        </div>

        <div class="loader js-loader">
            <div class="loader-container">
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
            </div>
        </div>

    <?php } elseif(!$arResult['IS_AJAX']) { ?>
        <h2>
            <?= LANG_ID == 'RU'
                ? "По выбранным фильтрам ничего не найдено"
                : "Nothing was found for the selected filters"; ?>
        </h2>
    <?php }
    if ($arResult['IS_AJAX']) { $GLOBALS['APPLICATION']->RestartBuffer(); die(); } ?>
</div>

<script>
    window.addEventListener("load", () => {
        [].forEach.call(document.querySelectorAll('#download_excel'), el =>
            el.addEventListener('click', e => {
                let body = new FormData();

                body.set('downloadExcel', 'wallpapers');
                body.set('wallpaperName', '<?= $request->get('wallpaperName'); ?>');
                body.set('tradeMark', '<?= $request->get('tradeMark'); ?>');
                body.set('collection', '<?= $request->get('collection'); ?>');

                fetch('<?= $APPLICATION->GetCurPage(); ?>', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: body
                }).then((response) => response.json())
                    .then((response) => {
                        window.location.href = response;
                    });
            }, false)
        );
    })
</script>
