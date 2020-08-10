<?php
// Production Server uses CDN for CodeMirror and JSHint
$cm_lib_root = 'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.48.0/';
$cm_root = $cm_lib_root;
$jshint_root = 'https://cdnjs.cloudflare.com/ajax/libs/jshint/2.10.2/';

// For Local Development you may want to download
// and work with the files offline:
//
// $cm_lib_root = $app->rootDir() . '/js/vendor/codemirror-5.48.0/lib/';
// $cm_root = $app->rootDir() . '/js/vendor/codemirror-5.48.0/';
// $jshint_root = $app->rootDir() . '/js/vendor/';
?>
<link rel="stylesheet" href="<?= $cm_lib_root ?>codemirror.css">
<link rel="stylesheet" href="<?= $cm_root ?>addon/lint/lint.css">

<style>
    /* Hide footer on this page to remove scrollbar for the page */
    footer { display:none; }

    .content {
        overflow: initial;
    }
    .editor-container {
        display: flex;
        min-height: calc(100vh - 245px);
        margin: auto 20px;
        background-color: #E2E4EF;
        background-image: linear-gradient(180deg, #fff 0%, #E2E4EF 100%);
        max-width: 1200px;
        margin: auto;
        position:relative;
    }
    .editor-container.warning {
        box-shadow: 0 0 40px 0 rgba(255,0,0,1);
    }
    @media screen and (min-width: 700px) {
        .editor-container {
            min-height: calc(100vh - 345px);
        }
    }
    @media screen and (min-width:1170px) {
        /* Between 700px and 1170px the nav menu will typically have 2 rows of buttons */
        .editor-container {
            min-height: calc(100vh - 245px);
        }
    }

    .editor-container .create-site-overlay {
        z-index:10;
        position:absolute;
        top: 0;
        left: 0;
        bottom: 0;
        right: 0;
        background-color:black;
        opacity: .1;
        border-radius: 4px;
    }

    .editor-container .create-site-screen,
    .editor-container .create-another-site-screen {
        z-index: 20;
        position: absolute;
        top: 0;
        left: 0;
        bottom: 0;
        right: 0;
        display: flex;
        align-items: center;
    }

    .create-site-screen .content,
    .create-another-site-screen .content {
        text-align:center;
    }

    .create-another-site-screen .content {
        max-width:500px;
    }
    .create-another-site-screen p {
        text-align:center;
        text-align: center;
        margin: 40px auto 50px;
        font-size: 1.4em;
    }

    .create-another-site-screen .site-expired { font-size: 1em; font-style: italic; }
    .create-another-site-screen p.try-other-site { font-size:1em; }

    h1 { margin-bottom:20px; font-size:2em; }

    .create-site-screen ul {
        text-align: left;
        margin-bottom: 20px;
        max-width: 500px;
    }

    .files,
    .file-info { display:none; }

    .content.mobile-message { margin:auto auto 30px auto; }

    /*
        Below 750 is for Mobile Layout and above is for Desktop.
        750px is determined based on size of content and layout.
    */
    @media screen and (min-width: 750px) {
        .files {
            display: flex;
            flex-direction: column;
        }
        .file-info { display:inline; }

        .content.mobile-message,
        .controls.mobile-file-list { display:none !important; }
    }

    .files ul {
        list-style-type: none;
        flex: 1 0 auto;
        padding-right: 20px;
        width:200px;
    }

    .files ul li {
        padding:5px 10px;
        padding-left: 0;
        cursor: pointer;
    }
    .files ul li::before {
        margin-right: 10px;
        padding: 0 8px 2px;
        color: white;
        width: 32px;
        display: inline-block;
        text-align: center;
        border-radius: 2em;
    }

    .files ul li.php::before { content:'PHP'; background-color:#4F5B93; }
    .files ul li.htm::before { content:'< >'; background-color:#e54c21; }
    .files ul li.js::before { content:'JS'; background-color:#f7df1e; }
    .files ul li.css::before { content:'{ }'; background-color:#214ce5; }
    .files ul li.svg::before { content:'○'; background-color:#ff9a00; }

    .files ul li.active {
        background-color: #4F5B93;
        color: white;
        border-radius: 4px;
        box-shadow: 0 0 2px rgba(0,0,0,.5);
        padding-left: 5px;
    }
    .files ul li.active::before { margin-right:5px; }

    .files ul li:hover {
        background-color: #8892BF;
        color:white;
        border-radius: 4px;
        padding-left: 5px;
    }

    .files ul li:first-child { margin-top:0; }

    .editor {
        width: 100%;
        display: flex;
        flex-direction: column;
    }

    .controls { margin-bottom:20px; min-height:36px; }
    .editor .controls {
        display: flex;
        justify-content: space-between;
    }
    .controls.countdown {
        margin-top: 30px;
        margin-bottom: 0;
    }
    .controls.delete {
        margin-top:10px;
        margin-bottom:0;
        display: flex;
        flex-direction: column;
        align-items: flex-start;
    }

    .editor textarea {
        height: calc(100vh - 305px);
        max-width: 980px;
    }

    .CodeMirror {
        height: calc(100vh - 305px);
        border:1px solid gray;
        width:100%;
    }
    @media screen and (min-width: 700px) {
        .CodeMirror {
            height: calc(100vh - 365px);
        }
    }
    @media screen and (min-width: 750px) {
        .CodeMirror {
            max-width:calc(100vw - 320px);
        }
    }
    @media screen and (min-width:1170px) {
        .CodeMirror {
            height: calc(100vh - 305px);
        }
    }
    @media screen and (min-width:1280px) {
        .CodeMirror {
            max-width: 980px;
        }
    }

    .no-site .CodeMirror {
        height: calc(100vh - 245px);
    }

    .editor.php .cm-s-default .cm-meta { background-color:yellow; }

    #countdown { color:green; margin-left:10px; }
    #countdown.warning {
        color: white;
        background-color: red;
        border-radius: 2em;
        padding: 8px 16px;
        font-weight: bold;
        margin-left: 0;
        box-shadow: 0 0 2px rgba(0,0,0,.5);
        display: inline-block;
    }
    #countdown.warning.scale {
        transform: scale(1.05);
    }

    .error {
        font-weight:bold;
        display: flex;
        justify-content: space-between;
    }
    .error .close-error {
        padding:5px 10px;
        float:right;
        border:1px solid darkred;
        cursor:pointer;
        margin-left:10px;
        background-image:linear-gradient(#c00,#a00);
        border-radius:5px;
    }

    .btn {
        cursor:pointer;
        border-radius: 4px;
        padding:5px 10px;
        color: #4F5B93;
        white-space: nowrap;
        display: inline-block;
    }
    .content a.btn { text-decoration:none; color:#4F5B93; }
    .btn:hover { background-color: #8892BF; color:white; }
    .content a.btn:hover { color:white; }

    .controls .btn { font-weight:bold; }

    .controls .btn,
    .controls .msg,
    .controls input { margin-right:20px; }

    .controls .msg {
        font-weight: bold;
        color: #8892BF;
    }

    .controls input[disabled] { color:gray; }

    .controls.delete .btn { margin-top:10px; }

    .controls .btn.view-site { margin-right:0; }

    .controls .btn {
        display: inline-flex;
        align-items: center;
    }
    .controls .btn .icon {
        width: 16px;
        height: 16px;
        margin-right: 12px;
        background-repeat: no-repeat;
    }
    .btn.add-file .icon { background-image:url('../img/buttons/add.svg'); }
    .btn.add-file:hover .icon { background-image:url('../img/buttons/add-hover.svg'); }
    .btn.delete-file .icon { background-image:url('../img/buttons/delete.svg'); }
    .btn.delete-file:hover .icon { background-image:url('../img/buttons/delete-hover.svg'); }
    .btn.delete-site .icon { background-image:url('../img/buttons/bomb.svg'); }
    .btn.delete-site:hover .icon { background-image:url('../img/buttons/bomb-hover.svg'); }
    .btn.view-site .icon { background-image:url('../img/buttons/view.svg'); }
    .btn.view-site:hover .icon { background-image:url('../img/buttons/view-hover.svg'); }
    .btn.view-file .icon { background-image:url('../img/buttons/view-item.svg'); }
    .btn.view-file:hover .icon { background-image:url('../img/buttons/view-item-hover.svg'); }
    .btn.save-file .icon { background-image:url('../img/buttons/save.svg'); }
    .btn.save-file:hover .icon { background-image:url('../img/buttons/save-hover.svg'); }
    .btn.rename-file .icon { background-image:url('../img/buttons/rename.svg'); }
    .btn.rename-file:hover .icon { background-image:url('../img/buttons/rename-hover.svg'); }

    .btn.create-site {
        background-color: #FFAB76;
        background-image: linear-gradient(hsla(23, 100%, 83%, 1), hsla(23, 100%, 63%, 1));
        display: inline-flex;
        font-weight: bold;
        text-decoration: none;
        cursor: pointer;
        box-shadow: 0 1px 2px 0 rgba(0,0,0,.5);
        color: white;
        border-radius: 32px;
        padding: 0;
        border: none;
        transition: all 0.2s;
    }
    .btn.create-site .text {
        padding: 15px 30px;
        font-size: 16px;
        line-height: 16px;
    }
    .btn.create-site .icon-container {
        background-color: hsla(23, 100%, 53%, 1);
        background-image: linear-gradient(hsla(23, 100%, 68%, 1), hsla(23, 100%, 48%, 1));
        border-top-right-radius: 32px;
        border-bottom-right-radius: 32px;
        transition: all 0.2s;
        display:flex;
        align-items:center;
    }
    .btn.create-site .arrow {
        display: inline-block;
        width: 0;
        height: 0;
        border-top: 8px solid transparent;
        border-left: 16px solid white;
        border-bottom: 8px solid transparent;
        margin: 16px;
    }
    .btn.create-site:hover {
        background-color: hsla(23, 100%, 53%, 1);
        background-image: linear-gradient(hsla(23, 100%, 68%, 1), hsla(23, 100%, 48%, 1));
        box-shadow: 0 2px 5px 0 rgba(0,0,0,.5);
        transform: translateY(-3px);
    }
    .btn.create-site:hover .icon-container {
        background-color: #FFAB76;
        background-image: linear-gradient(hsla(23, 100%, 83%, 1), hsla(23, 100%, 63%, 1));
    }
</style>

<section class="content mobile-message">
    <p
        data-mobile-screen="<?= $app->escape($i18n['mobile_layout']) ?>"
        data-narrow-screen="<?= $app->escape($i18n['narrow_layout']) ?>">
    </p>
</section>

<section class="content error-message" style="display:none;">
    <p class="error">
        <span class="text"></span>
        <span class="close-error">✕</span>
    </p>
</section>

<section class="content editor-container no-site">
    <div class="error-messages" style="display:none;"
        data-saved-site="<?= $app->escape($i18n['error_saved_site']) ?>"
        data-server-down="<?= $app->escape($i18n['error_server_down']) ?>">
    </div>
    <div class="create-site-overlay"></div>
    <div class="create-site-screen">
        <section class="content">
            <h1><?= $app->escape($i18n['h1']) ?></h1>
            <ul class="bullet-list">
                <li><?= $app->escape($i18n['info_1']) ?></li>
                <li><?= $app->escape($i18n['info_2']) ?></li>
                <li><?= $app->escape($i18n['info_3']) ?></li>
                <li><?= $app->escape($i18n['info_4']) ?></li>
                <li><?= $app->escape($i18n['info_5']) ?></li>
            </ul>
            <span class="btn create-site">
                <span class="text"><?= $app->escape($i18n['btn_create']) ?></span>
                <span class="icon-container">
                    <span class="arrow"></span>
                </span>
            </span>
        </section>
    </div>
    <div class="create-another-site-screen" style="display:none;">
        <section class="content">
            <h1><?= $app->escape($i18n['thank_you']) ?></h1>
            <p><?= $app->escape($i18n['please_share']) ?></p>
            <p class="site-expired"><?= $app->escape($i18n['site_expired']) ?></p>
            <span class="btn create-site">
                <span class="text"><?= $app->escape($i18n['btn_create_another']) ?></span>
                <span class="icon-container">
                    <span class="arrow"></span>
                </span>
            </span>
            <p class="try-other-site"><a href="https://www.dataformsjs.com/en/playground" target="_blank" rel="noopener"><?= $app->escape($i18n['try_other_site']) ?></a></p>
        </section>
    </div>
    <div class="files">
        <div class="controls" style="display:none;">
            <span class="btn add-file">
                <span class="icon"></span>
                <span class="text"><?= $app->escape($i18n['btn_add_file']) ?></span>
            </span>
            <span class="msg file-limit" style="display:none;"><?= $app->escape($i18n['file_limit_reached']) ?></span>
        </div>
        <ul></ul>
        <div class="controls countdown">
            <span id="countdown"
                data-minutes="<?= $app->escape($i18n['minutes_remaining']) ?>"
                data-seconds="<?= $app->escape($i18n['seconds_remaining']) ?>"
                data-one-second="<?= $app->escape($i18n['one_second_remaining']) ?>">
            </span>
        </div>
        <div class="controls delete" style="display:none;">
            <span class="btn delete-file" style="display:none;">
                <span class="icon"></span>
                <span class="text"><?= $app->escape($i18n['btn_delete_file']) ?></span>
            </span>
            <span class="btn delete-site" data-prompt="<?= $app->escape($i18n['confirm_site_delete']) ?>">
                <span class="icon"></span>
                <span class="text"><?= $app->escape($i18n['btn_delete_site']) ?></span>
            </span>
        </div>
    </div>
    <div class="editor">
        <div class="controls" style="display:none;">
            <span class="file-info">
                <input id="file-name" type="text" size="30" value="" placeholder="<?= $app->escape($i18n['file_name']) ?>">
                <select id="file-type" style="display:none">
                    <option value="php" data-mode="application/x-httpd-php" selected>PHP</option>
                    <option value="htm" data-mode="htmlmixed">HTML</option>
                    <option value="js" data-mode="text/javascript">JavaScript</option>
                    <option value="css" data-mode="text/css">CSS</option>
                    <option value="svg" data-mode="application/xml">SVG</option>
                </select>
            </span>
            <span>
                <span class="btn save-file" style="display:none;"
                    title="<?= $app->escape($i18n['save_file_tooltip']) ?>"
                    data-save="<?= $app->escape($i18n['btn_save_file']) ?>"
                    data-save-as="<?= $app->escape($i18n['btn_save_as_file']) ?>">
                    <span class="icon"></span>
                    <span class="text"></span>
                </span>
                <span class="btn rename-file" style="display:none;"
                    data-rename="<?= $app->escape($i18n['btn_rename_file']) ?>"
                    data-rename-and-save="<?= $app->escape($i18n['btn_rename_and_save_file']) ?>">
                    <span class="icon"></span>
                    <span class="text"></span>
                </span>
                <span class="msg file-event"
                    style="display:none;"
                    data-saved="<?= $app->escape($i18n['file_saved']) ?>"
                    data-deleted="<?= $app->escape($i18n['file_deleted']) ?>">
                </span>
                <span class="msg file-already-exists" style="display:none;"
                    data-already-exists="<?= $app->escape($i18n['file_already_exists']) ?>"
                    data-name-not-allowed="<?= $app->escape($i18n['name_not_allowed']) ?>">
                </span>
                <a class="btn view-file" href="#" target="_blank" rel="noopener" style="display:none;">
                    <span class="icon"></span>
                    <span class="text"><?= $app->escape($i18n['btn_view_file']) ?></span>
                </a>
                <a class="btn view-site" href="#" target="_blank" rel="noopener">
                    <span class="icon"></span>
                    <span class="text"><?= $app->escape($i18n['btn_view_site']) ?></span>
                </a>
            </span>
        </div>
        <div class="controls mobile-file-list" style="display:none;">
            <select id="file-list">
            </select>
        </div>
        <textarea id="code-editor"></textarea>
    </div>
</section>

<!-- JSHint and CodeMirror -->
<script src="<?= $jshint_root ?>jshint.min.js"></script>
<script src="<?= $cm_lib_root ?>codemirror.js"></script>
<script src="<?= $cm_root ?>addon/edit/matchbrackets.js"></script>
<script src="<?= $cm_root ?>mode/htmlmixed/htmlmixed.js"></script>
<script src="<?= $cm_root ?>mode/xml/xml.js"></script>
<script src="<?= $cm_root ?>mode/javascript/javascript.js"></script>
<script src="<?= $cm_root ?>mode/css/css.js"></script>
<script src="<?= $cm_root ?>mode/clike/clike.js"></script>
<script src="<?= $cm_root ?>mode/php/php.js"></script>
<script src="<?= $cm_root ?>addon/lint/lint.js"></script>
<script src="<?= $cm_root ?>addon/lint/javascript-lint.js"></script>

<!-- Playground App Code -->
<script src="<?= $app->rootDir() ?>js/playground.js"></script>
