<style>
    h2 { margin-bottom:10px; font-size: 1.4em; }
    .description > div { margin:20px; margin-top:0; }
    .description ul { margin-left: 2em; }
    .description li { line-height:1.5em; }
    .row { margin:20px; display:flex; align-items:flex-start; flex-direction:column; }
    .buttons,
    .key-controls { display:flex; align-items:flex-start; }
    .key-controls { border: 1px solid rgb(169, 169, 169); }
    input { width:600px; }
    label { font-weight:bold; margin:10px; margin-left:20px; }
    button { margin:20px 20px 0 0; }
    #key { width:calc(100% - 30px); border:0; }
    #text { width:calc(100% - 30px); }
    section.content.tab-container { width:90%; min-width:600px; max-width:1050px; padding:0; }
    section.content.tab-container.sample-code { max-width:600px; margin-top:40px; padding:20px; }
    @media screen and (min-width: 750px) {
        .row { flex-direction:row; }
        button { margin:0; margin-left:20px; }
    }
</style>

<div>
    <section class="content example-title inline-block center align-center">
        <h1><?= $app->escape($i18n['page_title']) ?></h1>
        <?php if (isset($app->config['ExamplesSite'])): ?>
            <div><a href="https://www.fastsitephp.com/<?= $app->lang ?>/api/Security_Crypto_Encryption" target="_blank" rel="noopener"><?= $app->escape($i18n['api_docs']) ?></a></div>
        <?php else: ?>
            <div><a href="<?= $app->rootUrl() . $app->lang ?>/api/Security_Crypto_Encryption"><?= $app->escape($i18n['api_docs']) ?></a></div>
        <?php endif ?>
    </section>
</div>

<section class="content tab-container inline-block center">
    <div class="tabs">
        <span class="tab active" data-target=".encrypt"><?= $app->escape($i18n['encrypt']) ?> / <?= $app->escape($i18n['decrypt']) ?></span>
        <span class="tab" data-target=".description"><?= $app->escape($i18n['info']) ?></span>
        <span class="tab" data-target=".code"><?= $app->escape($i18n['example_code']) ?></span>
    </div>
    <div class="tab-content encrypt">
        <div class="row error-message" style="display:none;">
            <p class="error"></p>
        </div>
        <div class="row">
            <div class="key-controls">
                <label for="key"><?= $app->escape($i18n['key']) ?></label>
                <textarea id="key" rows="3" cols="55"><?= $app->escape($key) ?></textarea>
            </div>
            <div class="buttons">
                <button id="btn-encrypt"><?= $app->escape($i18n['encrypt']) ?></button>
                <button id="btn-decrypt"><?= $app->escape($i18n['decrypt']) ?></button>
                <button id="btn-new-key"><?= $app->escape($i18n['new_key']) ?></button>
            </div>
        </div>
        <div class="row">
            <textarea id="text" rows="20" cols="80"><?= $app->escape($i18n['sample_text']) ?></textarea>
        </div>
    </div>
    <div class="tab-content description" style="display:none;">
        <div>
            <h2><?= $app->escape($i18n['using_page']) ?></h2>
            <ul>
                <li><?= $app->escape($i18n['using_page_1']) ?></li>
                <li><?= $app->escape($i18n['using_page_2']) ?></li>
                <li><?= $app->escape($i18n['using_page_3']) ?></li>
                <li><?= $app->escape($i18n['using_page_4']) ?></li>
                <li><?= $app->escape($i18n['using_page_5']) ?></li>
                <li><?= $app->escape($i18n['using_page_6']) ?></li>
            </ul>
        </div>
        <div>
            <h2><?= $app->escape($i18n['algo_overview']) ?></h2>
            <ul>
                <li><?= $app->escape($i18n['algo_overview_1']) ?></li>
                <li><?= $app->escape($i18n['algo_overview_2']) ?></li>
                <li><?= $app->escape($i18n['algo_overview_3']) ?></li>
            </ul>
        </div>
    </div>
    <div class="tab-content code align-center" style="display:none;">
        <section class="content sample-code">
            <pre><code class="language-php"><?= $app->escape($i18n['code']) ?></code></pre>
        </section>
    </div>
</section>
