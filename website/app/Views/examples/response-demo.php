<style>
    section.content { width:90%; max-width:900px; }
    .response,
    .code { margin-top:40px; max-width:700px; }
</style>

<div>
    <section class="content example-title center align-center">
        <h1><?= $app->escape($i18n['page_title']) ?></h1>
        <?php if (isset($app->config['ExamplesSite'])): ?>
            <a href="https://www.fastsitephp.com/<?= $app->lang ?>/api/Web_Response" target="_blank" rel="noopener"><?= $app->escape($i18n['api_docs']) ?></a>
        <?php else: ?>
            <a href="<?= $app->rootUrl() . $app->lang ?>/api/Web_Response"><?= $app->escape($i18n['api_docs']) ?></a>
        <?php endif ?>
    </section>
</div>

<section class="content align-center">
    <div>
        <select>
            <option value="html"><?= $app->escape($i18n['html']) ?></option>
            <option value="json"><?= $app->escape($i18n['json']) ?></option>
            <option value="text"><?= $app->escape($i18n['text']) ?></option>
        </select>
    </div>
    <div class="response center">
        <section class="content sample-code">
            <h2><?= $app->escape($i18n['response']) ?></h2>
            <pre><code class="result"></code></pre>
        </section>
    </div>
    <div class="code center">
        <section class="content sample-code">
            <h2><?= $app->escape($i18n['example_code']) ?></h2>
            <pre><code class="language-php" id="example-code"></code></pre>
        </section>
    </div>
</section>

<code id="tmpl-html" style="display:none;"><?= $app->escape($i18n['tmpl_html']) ?></code>
<code id="tmpl-json" style="display:none;"><?= $app->escape($i18n['tmpl_json']) ?></code>
<code id="tmpl-text" style="display:none;"><?= $app->escape($i18n['tmpl_text']) ?></code>
