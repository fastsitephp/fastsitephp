<style>
    p { text-align:left; }
    .filter { margin:0; width:100%; max-width:calc(100% - 40px); margin-bottom:20px; }
    @media (min-width:800px) {
        .filter { margin:20px; max-width:600px; }
    }    
    .filter-info { font-weight:bold; }
    .sample-code .token.delimiter { background-color:inherit; padding:0; border-radius:0; }
    .content.form {
        text-align: center;
        max-width: 800px;
        margin-bottom: 80px;
    }
</style>

<div>
    <section class="content page-title">
        <h1><?= $app->escape($i18n['page_title']) ?></h1>
    </section>
</div>

<section class="content form">
    <p><?= $app->escape($i18n['page_description_1']) ?></p>
    <p><?= $app->escape($i18n['page_description_2']) ?></p>
    <input class="filter" 
        disabled
        data-filter=".content.sample-code"
        data-placeholder="<?= $app->escape($i18n['filter_placeholder']) ?>"
        data-disabled="<?= $app->escape($i18n['filter_disabled']) ?>">
    <div class="filter-info" 
        data-text-all="<?= $app->escape($i18n['filter_all']) ?>"
        data-text-filtered="<?= $app->escape($i18n['filter_filtered']) ?>">
        <?= str_replace('{count}', count($example_code), $app->escape($i18n['filter_all'])) ?>
    </div>
</section>

<?php foreach ($example_code as $code): ?>
    <section class="content sample-code">
        <h2><?= $app->escape($code->title) ?></h2>
        <pre><code class="language-php"><?= $app->escape($code->code) ?></code></pre>
    </section>
<?php endforeach ?>
