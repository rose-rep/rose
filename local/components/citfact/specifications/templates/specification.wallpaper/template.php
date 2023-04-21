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
            <button class="download-excel download-excel--with-filters" id="download_excel" type="button">
                <span class="download-excel__text">
                    <?= Loc::getMessage('EXPORT'); ?>
                </span>
                <svg class="download-excel__icon" fill="none" viewBox="0 0 23 26" id="excel" xmlns="http://www.w3.org/2000/svg">
                    <path d="M4.18 1.436h13.06l5.26 6.567v15.863H4.18V1.436z" stroke="#1C2C33"/>
                    <path fill="#fff" stroke="#1C2C33" d="M.5 12.039h12.8v12.846H.5z"/>
                    <path d="M2.76 22.615l3.724-4.711v.914l-3.52-4.51h1.46l2.79 3.548-.577.012 2.778-3.56h1.394l-3.493 4.438v-.854l3.724 4.723H9.568L6.6 18.83h.563l-2.93 3.785H2.76z" fill="#1C2C33"/>
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
                                <?php foreach ($arResult['TRADEMARKS'] as $tradeMark) { ?>
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
                                <svg class="static-actions__filters-clear-icon" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M13.8486 3.9502L3.94914 13.8497" stroke-linecap="round"/>
                                    <path d="M13.8486 3.9502L3.94914 13.8497" stroke-linecap="round"/>
                                    <path d="M13.8486 13.8496L3.94914 3.95011" stroke-linecap="round"/>
                                    <path d="M13.8486 13.8496L3.94914 3.95011" stroke-linecap="round"/>
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
                            <svg width="18" height="18" viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="8.24968" cy="8.20085" r="6.5" transform="rotate(-30 8.24968 8.20085)" />
                                <path d="M12.817 17.1108C12.9551 17.3499 13.2609 17.4319 13.5 17.2938C13.7391 17.1557 13.8211 16.8499 13.683 16.6108L12.817 17.1108ZM12.183 14.0127L11.933 13.5797L11.067 14.0797L11.317 14.5127L12.183 14.0127ZM13.683 16.6108L12.183 14.0127L11.317 14.5127L12.817 17.1108L13.683 16.6108Z" />
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
                                <?/*= $code; */?><!--<br>-->
                                <?= Loc::getMessage($code); ?>
                            </div>
                        <?php } ?>
                    <?php } ?>
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
                            <svg width="19" height="18" viewBox="0 0 19 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M2.57031 4L9.57031 13L16.5703 4" stroke="black" stroke-linecap="round"/>
                                <path d="M2.57031 4L9.57031 13L16.5703 4" stroke="black" stroke-linecap="round"/>
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
