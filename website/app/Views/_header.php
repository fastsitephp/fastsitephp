<!DOCTYPE html>
<html lang="<?= $app->lang ?>">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

        <title>FastSitePHP | <?= (isset($page_title) ? $app->escape($page_title) : $app->escape($i18n['page_title'])) ?></title>
        <?php if (isset($i18n) && isset($i18n['page_desc'])): ?>
            <meta name="description" content="<?= $app->escape($i18n['page_desc']) ?>">
        <?php endif ?>

        <link rel="shortcut icon" href="<?= $app->rootDir() ?>favicon.ico" type="image/x-icon" />
        <link type="text/plain" rel="author" href="<?= $app->rootDir() ?>humans.txt" />

        <link rel="apple-touch-icon" href="<?= $app->rootDir() ?>img/favicons/FastSitePHP-76x76.png">
        <link rel="apple-touch-icon" sizes="152x152" href="<?= $app->rootDir() ?>img/favicons/FastSitePHP-152x152.png">
        <link rel="apple-touch-icon" sizes="167x167" href="<?= $app->rootDir() ?>img/favicons/FastSitePHP-167x167.png">
        <link rel="apple-touch-icon" sizes="180x180" href="<?= $app->rootDir() ?>img/favicons/FastSitePHP-180x180.png">

        <link rel="icon" sizes="32x32" href="<?= $app->rootDir() ?>img/favicons/FastSitePHP-32x32.png">
        <link rel="icon" sizes="144x144" href="<?= $app->rootDir() ?>img/favicons/FastSitePHP-144x144.png">
        <link rel="icon" sizes="192x192" href="<?= $app->rootDir() ?>img/favicons/FastSitePHP-192x192.png">
        <link rel="icon" sizes="196x196" href="<?= $app->rootDir() ?>img/favicons/FastSitePHP-196x196.png">

        <link href="<?= $app->rootDir() ?>js/prism.css" rel="stylesheet" />
        <?= $site_css ?>
        <script>
            // Set attribute on root <html> attribute for Samsung devices. Used for CSS.
            // See [fastsitephp/website/public/css/site.css]: "html.samsung body"
            (function() {
                var isSamsung = (navigator.userAgent.toLowerCase().indexOf('samsungbrowser') > -1);
                if (isSamsung) {
                    document.documentElement.classList.add('samsung');
                }
            })();
        </script>
        <script nomodule>
            (function() {
                'use strict';
                var isIE = (navigator.userAgent.indexOf('Trident/') !== -1);
                if (isIE) {
                    // IE only - fixed nav layout when menu items wrap to 2 lines
                    var style = document.createElement('style');
                    var css = '@media screen and (-ms-high-contrast: active), (-ms-high-contrast: none) {';
                    css += '    .site-nav { flex: 1 0 auto; }'
                    css += '}';
                    style.innerHTML = css;
                    document.head.appendChild(style);
                }
            })();
        </script>
    </head>
    <body>
        <header>
            <?php
            // Variables needed for nav links
            switch ($app->lang) {
                case 'es':
                case 'pt-BR':
                    $github = 'https://github.com/fastsitephp/fastsitephp/blob/master/docs/i18n-readme/README.' . $app->lang . '.md';
                    break;
                default:
                    $github = 'https://github.com/fastsitephp/fastsitephp';
            }

            // Once full translations are made this will go at the top of the file for the <html> element
            $html_dir = ($app->lang === 'ar' ? 'rtl' : 'ltr');

            $current_page = $app->requestedPath();
            if ($current_page !== '') {
                $components = explode('/', $current_page);
                $current_page = substr($current_page, strlen($components[1]) + 1);
            }
            if ($current_page === '') {
                $current_page = '/';
            }

            $is_resources_page = (
                isset($nav_active_link)
                && (
                    $nav_active_link === 'quick-reference'
                    || $nav_active_link === 'examples'
                    || $nav_active_link === 'documents'
                    || $nav_active_link === 'api'
                )
            );
            ?>
            <nav class="site-nav" dir="<?= $html_dir ?>">
                <div class="mobile-nav">
                    <span class="site-title"><a href="<?= $app->rootUrl() . $app->lang ?>/">
                        <svg width="16px" height="16px" viewBox="0 0 16 16" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                            <g id="Buttons" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                <g id="home" fill="#4F5B93">
                                    <rect id="Rectangle" x="2" y="10" width="12" height="6"></rect>
                                    <polygon id="Triangle" points="8 4 14 10 2 10"></polygon>
                                    <rect id="Rectangle" transform="translate(11.500000, 4.750000) rotate(45.000000) translate(-11.500000, -4.750000) " x="6" y="4" width="11" height="1.5" rx="0.75"></rect>
                                    <rect id="Rectangle" transform="translate(4.500000, 4.750000) scale(-1, 1) rotate(45.000000) translate(-4.500000, -4.750000) " x="-1" y="4" width="11" height="1.5" rx="0.75"></rect>
                                    <polygon id="Rectangle" points="12 0 14 0 14 5 12 3"></polygon>
                                </g>
                            </g>
                        </svg>
                        <span style="margin-left:8px;">FastSitePHP</span>
                    </a></span>
                    <span class="open-menu"><a href="#">
                        <svg width="16px" height="16px" viewBox="0 0 16 16" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                            <g id="Buttons" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                <g id="open-menu" transform="translate(0.000000, -1.000000)" fill="#4F5B93">
                                    <rect id="Rectangle" x="0" y="2" width="16" height="3" rx="1.5"></rect>
                                    <rect id="Rectangle" x="0" y="8" width="16" height="3" rx="1.5"></rect>
                                    <rect id="Rectangle" x="0" y="14" width="16" height="3" rx="1.5"></rect>
                                </g>
                            </g>
                        </svg>
                    </a></span>
                </div>
                <div class="mobile-menu" style="display:none;">
                    <div>
                        <span class="close-menu"><a href="#">
                            <svg width="16px" height="16px" viewBox="0 0 16 16" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                                <g id="Buttons" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                    <g id="close-menu" fill="#4F5B93">
                                        <rect id="Rectangle" transform="translate(7.707107, 8.207107) rotate(45.000000) translate(-7.707107, -8.207107) " x="-2.29289322" y="6.70710678" width="20" height="3" rx="1.5"></rect>
                                        <rect id="Rectangle" transform="translate(8.131728, 8.131728) scale(-1, 1) rotate(45.000000) translate(-8.131728, -8.131728) " x="-1.86827202" y="6.63172798" width="20" height="3" rx="1.5"></rect>
                                    </g>
                                </g>
                            </svg>
                        </a></span>
                    </div>
                    <ul>
                        <li<?= (isset($nav_active_link) && $nav_active_link === 'playground' ? ' class="active"' : '') ?>>
                            <a href="<?= $app->rootUrl() . $app->lang ?>/playground"><?= $app->escape($i18n['menu_playground']) ?></a>
                        </li>
                        <li<?= (isset($nav_active_link) && $nav_active_link === 'getting-started' ? ' class="active"' : '') ?>>
                            <a href="<?= $app->rootUrl() . $app->lang ?>/getting-started"><?= $app->escape($i18n['menu_getting_started']) ?></a>
                        </li>
                        <li<?= (isset($nav_active_link) && $nav_active_link === 'quick-reference' ? ' class="active"' : '') ?>>
                            <a href="<?= $app->rootUrl() . $app->lang ?>/quick-reference"><?= $app->escape($i18n['menu_quick_ref']) ?></a>
                        </li>
                        <li<?= (isset($nav_active_link) && $nav_active_link === 'examples' ? ' class="active"' : '') ?>>
                            <a href="<?= $app->rootUrl() . $app->lang ?>/examples"><?= $app->escape($i18n['menu_examples']) ?></a>
                        </li>
                        <li<?= (isset($nav_active_link) && $nav_active_link === 'documents' ? ' class="active"' : '') ?>>
                            <a href="<?= $app->rootUrl() . $app->lang ?>/documents"><?= $app->escape($i18n['menu_documents']) ?></a>
                        </li>
                        <li<?= (isset($nav_active_link) && $nav_active_link === 'api' ? ' class="active"' : '') ?>>
                            <a href="<?= $app->rootUrl() . $app->lang ?>/api"><?= $app->escape($i18n['menu_api']) ?></a>
                        </li>
                        <li>
                            <a href="<?= $github ?>" class="github" target="_blank" rel="noopener">
                            <img src="<?= $app->rootDir() ?>img/logos/GitHub-Mark-32px.png" alt="GitHub" height="32" width="32">
                            </a>
                        </li>
                    </ul>
                    <ul class="i18n-menu">
                        <li<?= ($app->lang === 'en' ? ' class="active"' : '') ?>>
                            <a href="<?= $app->escape($app->rootUrl() . 'en' . $current_page) ?>">English</a>
                        </li>
                        <li<?= ($app->lang === 'es' ? ' class="active"' : '') ?>>
                            <a href="<?= $app->escape($app->rootUrl() . 'es' . $current_page) ?>">Español</a>
                        </li>
                        <li<?= ($app->lang === 'fr' ? ' class="active"' : '') ?>>
                            <a href="<?= $app->escape($app->rootUrl() . 'fr' . $current_page) ?>">Français</a>
                        </li>
                        <li<?= ($app->lang === 'pt-BR' ? ' class="active"' : '') ?>>
                            <a href="<?= $app->escape($app->rootUrl() . 'pt-BR' . $current_page) ?>">Português (Brasil)</a>
                        </li>
                        <li<?= ($app->lang === 'ar' ? ' class="active"' : '') ?>>
                            <a href="<?= $app->escape($app->rootUrl() . 'ar' . $current_page) ?>">العربية</a>
                        </li>
                        <li<?= ($app->lang === 'zh-CN' ? ' class="active"' : '') ?>>
                            <a href="<?= $app->escape($app->rootUrl() . 'zh-CN' . $current_page) ?>">中文 (简体)</a>
                        </li>
                    </ul>
                </div>
                <ul class="desktop-nav">
                    <li<?= (isset($nav_active_link) && $nav_active_link === 'home' ? ' class="active"' : '') ?>>
                        <a href="<?= $app->rootUrl() . $app->lang ?>/"><?= $app->escape($i18n['menu_home']) ?></a>
                    </li>
                    <li<?= (isset($nav_active_link) && $nav_active_link === 'playground' ? ' class="active"' : '') ?>>
                        <a href="<?= $app->rootUrl() . $app->lang ?>/playground"><?= $app->escape($i18n['menu_playground']) ?></a>
                    </li>
                    <li<?= (isset($nav_active_link) && $nav_active_link === 'getting-started' ? ' class="active"' : '') ?>>
                        <a href="<?= $app->rootUrl() . $app->lang ?>/getting-started"><?= $app->escape($i18n['menu_getting_started']) ?></a>
                    </li>
                    <li class="sub-menu resources <?=($is_resources_page ? 'active' : '')?>">
                        <span><?= $app->escape($i18n['menu_resources']) ?></span>
                        <ul>
                            <li<?= (isset($nav_active_link) && $nav_active_link === 'quick-reference' ? ' class="active"' : '') ?>>
                                <a href="<?= $app->rootUrl() . $app->lang ?>/quick-reference"><?= $app->escape($i18n['menu_quick_ref']) ?></a>
                            </li>
                            <li<?= (isset($nav_active_link) && $nav_active_link === 'examples' ? ' class="active"' : '') ?>>
                                <a href="<?= $app->rootUrl() . $app->lang ?>/examples"><?= $app->escape($i18n['menu_examples']) ?></a>
                            </li>
                            <li<?= (isset($nav_active_link) && $nav_active_link === 'documents' ? ' class="active"' : '') ?>>
                                <a href="<?= $app->rootUrl() . $app->lang ?>/documents"><?= $app->escape($i18n['menu_documents']) ?></a>
                            </li>
                            <li<?= (isset($nav_active_link) && $nav_active_link === 'api' ? ' class="active"' : '') ?>>
                                <a href="<?= $app->rootUrl() . $app->lang ?>/api"><?= $app->escape($i18n['menu_api']) ?></a>
                            </li>
                        </ul>
                    </li>
                    <li>
                        <a href="<?= $github ?>" class="github" target="_blank" rel="noopener">
                        <img src="<?= $app->rootDir() ?>img/logos/GitHub-Mark-Light-32px.png" alt="GitHub" height="32" width="32">
                        </a>
                    </li>
                    <li class="sub-menu i18n-menu">
                        <div>
                            <svg width="24px" height="24px" viewBox="0 0 24 24" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                                <g id="Buttons" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                    <g id="i18n" stroke="#4F5B93">
                                        <circle id="Oval" fill="#FFFFFF" cx="12" cy="12" r="11.5"></circle>
                                        <path d="M12.5,-2.5 C11.8333333,-0.472541163 11.5,2.52745884 11.5,6.5 C11.5,12.4588117 12.5,15.5 12.5,15.5" id="Line" stroke-linecap="square" transform="translate(12.000000, 6.500000) rotate(-90.000000) translate(-12.000000, -6.500000) "></path>
                                        <ellipse id="Oval" cx="12" cy="12" rx="5.5" ry="11.5"></ellipse>
                                        <line x1="12" y1="0" x2="12" y2="24" id="Line-2" stroke-linecap="square"></line>
                                        <path d="M12.5,1 C11.8333333,4.83333333 11.5,8.66666667 11.5,12.5 C11.5,16.3333333 11.8333333,20.1666667 12.5,24" id="Line-2" stroke-linecap="square" transform="translate(12.000000, 12.500000) rotate(-90.000000) translate(-12.000000, -12.500000) "></path>
                                        <path d="M12.5,8.5 C11.8333333,11.6666667 11.5,14.8333333 11.5,18 C11.5,21.1666667 11.8333333,24.3333333 12.5,27.5" id="Line" stroke-linecap="square" transform="translate(12.000000, 18.000000) rotate(-90.000000) translate(-12.000000, -18.000000) "></path>
                                    </g>
                                </g>
                            </svg>
                            <span><?= $app->escape($app->lang) ?></span>
                        </div>
                        <ul>
                            <li<?= ($app->lang === 'en' ? ' class="active"' : '') ?>>
                                <a href="<?= $app->escape($app->rootUrl() . 'en' . $current_page) ?>">English</a>
                            </li>
                            <li<?= ($app->lang === 'es' ? ' class="active"' : '') ?>>
                                <a href="<?= $app->escape($app->rootUrl() . 'es' . $current_page) ?>">Español</a>
                            </li>
                            <li<?= ($app->lang === 'fr' ? ' class="active"' : '') ?>>
                                <a href="<?= $app->escape($app->rootUrl() . 'fr' . $current_page) ?>">Français</a>
                            </li>
                            <li<?= ($app->lang === 'pt-BR' ? ' class="active"' : '') ?>>
                                <a href="<?= $app->escape($app->rootUrl() . 'pt-BR' . $current_page) ?>">Português (Brasil)</a>
                            </li>
                            <li<?= ($app->lang === 'ar' ? ' class="active"' : '') ?>>
                                <a href="<?= $app->escape($app->rootUrl() . 'ar' . $current_page) ?>">العربية</a>
                            </li>
                            <li<?= ($app->lang === 'zh-CN' ? ' class="active"' : '') ?>>
                                <a href="<?= $app->escape($app->rootUrl() . 'zh-CN' . $current_page) ?>">中文 (简体)</a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </nav>
        </header>
        <main>
