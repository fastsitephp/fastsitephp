<style>
    section.content { display:inline-block; }
    .getting-started { display:flex; flex-direction:column; }
    .getting-started section.content { margin:20px 0; padding:0; overflow:auto; }
    .getting-started section.content div {
        background-color: #ffbc91;
        background-image: url('../img/card-background.svg');
        background-size: cover;
        height: 120px;
        border-top-left-radius: 4px;
        border-top-right-radius: 4px;
        background-position-y: center;
        display: flex;
        justify-content: center;
        align-items: center;
        font-weight: bold;
        font-size: 1.5em;
        text-align: center;
    }
    .getting-started section.content ul { padding:20px; list-style-type:none; }
    .getting-started section.content ul li {
        display:flex;
    }
    .getting-started section.content ul li a {
        display: flex;
        padding: 10px;
        text-decoration: none;
        font-weight: bold;
    }
    .getting-started section.content ul li a.download-icon {
        display: inline-flex;
        font-weight: normal;
        font-size: 1.4em;
    }
    .getting-started section.content ul li a:hover {
        background-color: #8892BF;
        box-shadow:0 1px 3px 0 rgba(0, 0, 0, .3);
        color: white;
        transition:all 0.1s;
    }
    .getting-started section.content span.text { margin-left:20px; margin-top:5px; }

    .getting-started section.content.php ul li img,
    .getting-started section.content.develop ul li img,
    .getting-started section.content.composer ul li img { height:32px; width:32px; }

    .getting-started section.content.composer ul li {
        align-items: center;
    }
    
    .getting-started section.content.download > div > span,
    .getting-started section.content.develop > div > span,
    .getting-started section.content.composer > div > span {
        background-color: white;
        padding: 0.5em 1em;
        box-shadow: 0 0 1px hsla(23, 100%, 40%, 0.5),
                    inset 0 0 2px 1px hsla(23, 100%, 30%, 0.8),
                    inset 0 0 4px 2px hsla(23, 100%, 40%, 0.8);
        text-shadow: 1px 1px 2px hsla(229, 30%, 64%, 1);
        transition:all 0.2s;
    }

    .getting-started section.content.download:hover > div > span,
    .getting-started section.content.develop:hover > div > span,
    .getting-started section.content.composer:hover > div > span {
        box-shadow: 0 0 1px hsla(23, 100%, 40%, 0.5),
                    inset 0 0 6px 3px hsla(23, 100%, 30%, 0.8),
                    inset 0 0 12px 6px hsla(23, 100%, 40%, 0.8);
    }

    img[src$="/php.svg"] { height:80px; }


    @media screen and (min-width: 1060px) {
        .getting-started {
            flex-direction:row;
            margin-top:40px;
            flex-wrap:wrap;
            justify-content: center;
            align-items: flex-start;
        }
        .getting-started section.content { margin:20px; }
        .getting-started section.content span.text { white-space:nowrap; }
    }

    @media screen and (min-width: 1400px) {
        .getting-started section.content { margin:40px; }
        .getting-started section.content ul { padding:40px; }
    }

    code {
        background-color: #fff;
        padding: 10px;
    }
</style>

<div>
    <section class="content page-title">
        <h1><?= $app->escape($i18n['page_title']) ?></h1>
    </section>
</div>

<div class="getting-started">
    <section class="content php">
        <div>
            <img src="../img/logos/php.svg" alt="PHP">
        </div>
        <ul>
           <li><a href="<?= $app->rootUrl() . $app->lang ?>/documents/install-php-on-linux"><img src="../img/logos/linux.svg" alt="Linux"><span class="text"><?= $app->escape($i18n['install_nix']) ?></span></a></li>
           <li><a href="<?= $app->rootUrl() . $app->lang ?>/documents/install-php-on-windows"><img src="../img/logos/Windows_logo_-_2012.svg" alt="Windows"><span class="text"><?= $app->escape($i18n['install_win']) ?></span></a></li>
           <li><a href="<?= $app->rootUrl() . $app->lang ?>/documents/install-php-on-mac"><img src="../img/logos/apple.svg" alt="Apple"><span class="text"><?= $app->escape($i18n['install_mac']) ?></span></a></li>
        </ul>
    </section>

    <section class="content download">
        <div>
            <span><?= $app->escape($i18n['download']) ?></span>
        </div>
        <ul>
            <li>
                <a href="<?= $app->rootUrl() ?>downloads/fastsitephp" class="download-icon" title="<?= $app->escape($i18n['download']) . ' ' . $app->escape($i18n['framework_and_site']) ?>">📥</a>
                <a href="https://github.com/fastsitephp/fastsitephp" target="_blank">
                    <img src="../img/logos/GitHub-Mark-32px.png" alt="GitHub" height="32" width="32">
                    <span class="text"><?= $app->escape($i18n['framework_and_site']) ?></span>
                </a>
            </li>
            <li>
                <a href="<?= $app->rootUrl() ?>downloads/starter-site" class="download-icon" title="<?= $app->escape($i18n['download']) . ' ' . $app->escape($i18n['starter_site']) ?>">📥</a>
                <a href="https://github.com/fastsitephp/starter-site" target="_blank">
                    <img src="../img/logos/GitHub-Mark-32px.png" alt="GitHub" height="32" width="32">
                    <span class="text"><?= $app->escape($i18n['starter_site']) ?></span>
                </a>
            </li>
            <li>
                <a href="<?= $app->rootUrl() ?>downloads/framework" class="download-icon" title="<?= $app->escape($i18n['download']) . ' ' . $app->escape($i18n['framework_only']) ?>">📥</a>
                <a href="https://github.com/fastsitephp/fastsitephp/releases" target="_blank">
                    <img src="../img/logos/GitHub-Mark-32px.png" alt="GitHub" height="32" width="32">
                    <span class="text"><?= $app->escape($i18n['framework_only']) ?></span>
                </a>
            </li>
        </ul>
    </section>

    <section class="content develop">
        <div>
            <span><?= $app->escape($i18n['develop']) ?></span>
        </div>
        <ul>
            <li>
                <a href="<?= $app->rootUrl() . $app->lang ?>/documents/edit-with-vs-code">
                    <img src="../img/logos/vs-code.png" alt="Visual Studio Code">
                    <span class="text"><?= $app->escape($i18n['vs_code']) ?></span>
                </a>
            </li>
            <li>
                <a href="<?= $app->rootUrl() . $app->lang ?>/documents/edit-with-atom">
                    <img src="../img/logos/atom.png" alt="Atom Editor">
                    <span class="text"><?= $app->escape($i18n['atom']) ?></span>
                </a>
            </li>
            <li>
                <a href="<?= $app->rootUrl() . $app->lang ?>/documents/edit-with-other">
                    <img src="../img/icons/Code-Editor.svg" alt="<?= $app->escape($i18n['code_editors']) ?>">
                    <span class="text"><?= $app->escape($i18n['other_editors']) ?></span>
                </a>
            </li>
        </ul>
    </section>

    <section class="content composer">
        <div>
            <span><?= $app->escape($i18n['package_manager']) ?></span>
        </div>
        <ul>
            <li>
                <a href="https://packagist.org/packages/fastsitephp/fastsitephp" target="_blank"><img src="../img/logos/packagist.png" alt="PHP Packagist"></a>
                <code>composer require fastsitephp/fastsitephp</code>
            </li>
            <li>
                <a href="https://packagist.org/packages/fastsitephp/starter-site" target="_blank"><img src="../img/logos/packagist.png" alt="PHP Packagist"></a>
                <code>composer create-project fastsitephp/starter-site my-app</code>
            </li>
        </ul>
    </section>
</div>
