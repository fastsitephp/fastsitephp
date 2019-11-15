<style>
    .intro-text {
        display: block;
        text-align: left;
        max-width: 700px;
        margin: auto;
        padding: 0 20px 20px 20px;     
    }

    .content.form {
        display: inline-flex;
        margin-bottom:10px;
        flex-direction: column;
    }

    .request-buttons { display:inline-block; margin:auto; }
    button { margin:auto 10px; margin-top: 20px; }
    @media (min-width:650px) {
        button { margin-top:auto; }
    }

    .color-list { list-style-type:none; display:flex; margin:auto; margin-bottom:20px; }
    .color-list li { padding:5px 10px 10px 10px; cursor:pointer; margin:auto 10px; user-select:none; }
    .color-list li.active { border:1px solid #4F5B93; }

    .color-list li::before {
        display: inline-block;
        width: 1em;
        height: 1em;
        content: '';
        user-select: none;
        margin-right: 0.5em;
        vertical-align: middle;
    }
    .color-list li[data-value='red']::before { background-color:red; }
    .color-list li[data-value='green']::before { background-color:green; }
    .color-list li[data-value='blue']::before { background-color:blue; }
    .color-list li[data-value='yellow']::before { background-color:yellow; }
    
    .sample-code .token.delimiter {
        background-color: white;
        padding: 0;
        border-radius: 0;
    }

    .color { position:absolute; display:inline-block; width:1em; height:1em; top: 0.45em; }
    .color.red { background-color:red; }
    .color.green { background-color:green; }
    .color.blue { background-color:blue; }
    .color.yellow { background-color:yellow; }
    .color.default { background-color:#8892BF; }
    .color.black { background-color:black; }

    td.url { position:relative; }
    span.url { margin-left:1.5em; }

    .content.code-container { width:90%; max-width:900px; background-color:hsla(230, 29%, 85%, 1); }
    .code { margin-top:40px; max-width:760px; }
</style>

<div>
    <section class="content example-title center align-center">
        <h1><?= $app->escape($i18n['page_title']) ?></h1>
        <?php if (isset($app->config['ExamplesSite'])): ?>
            <a href="https://www.fastsitephp.com/<?= $app->lang ?>/api/Data_Database" target="_blank"><?= $app->escape($i18n['api_docs']) ?></a>
        <?php else: ?>
            <a href="<?= $app->rootUrl() . $app->lang ?>/api/Data_Database"><?= $app->escape($i18n['api_docs']) ?></a>
        <?php endif ?>
    </section>
</div>

<section class="content align-center">
    <ul class="intro-text">
        <li><?= $app->escape($i18n['intro_text_1']) ?></li>
        <li><?= $app->escape($i18n['intro_text_2']) ?></li>
        <li><?= $app->escape($i18n['intro_text_3']) ?></li>
        <li><?= $app->escape($i18n['intro_text_4']) ?></li>
    </ul>
    <section class="content form">
        <ul class="color-list">
            <li class="active" data-value="red"><?= $app->escape($i18n['red']) ?></li>
            <li data-value="green"><?= $app->escape($i18n['green']) ?></li>
            <li data-value="blue"><?= $app->escape($i18n['blue']) ?></li>
            <li data-value="yellow"><?= $app->escape($i18n['yellow']) ?></li>
        </ul>
        <div class="request-buttons">
            <button type="button" class="request"><?= $app->escape($i18n['get']) ?></button>
            <button type="button" class="request"><?= $app->escape($i18n['post']) ?></button>
            <button type="button" class="request"><?= $app->escape($i18n['put']) ?></button>
            <button type="button" class="request"><?= $app->escape($i18n['delete']) ?></button>
        </div>
    </section>
    <table class="can-highlight">
        <thead>
            <tr>
                <th><?= $app->escape($i18n['id']) ?></th>
                <th><?= $app->escape($i18n['url']) ?></th>
                <th><?= $app->escape($i18n['method']) ?></th>
                <th><?= $app->escape($i18n['device_type']) ?></th>
                <th><?= $app->escape($i18n['os_type']) ?></th>
                <th title="<?= $app->escape($i18n['op_sys']) ?>"><?= $app->escape($i18n['os']) ?></th>
                <th><?= $app->escape($i18n['browser']) ?></th>
                <th><?= $app->escape($i18n['user_agent']) ?></th>
                <th><?= $app->escape($i18n['date_requested']) ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($records as $record): ?>
            <tr>
                <td class="nobr"><?= $app->escape($record['id']) ?></td>
                <td class="nobr url">
                    <span class="color <?= $app->escape($record['class_name']) ?>"></span>
                    <span class="url"><?= $app->escape($record['url']) ?></span>
                </td>
                <td><?= $app->escape($record['method']) ?></td>
                <td><?= $app->escape($record['device_type']) ?></td>
                <td><?= $app->escape($record['os_type']) ?></td>
                <td class="nobr"><?= $app->escape($record['os']) ?></td>
                <td><?= $app->escape($record['browser']) ?></td>
                <td><?= $app->escape($record['user_agent']) ?></td>
                <td class="nobr"><?= $app->escape($record['date_requested']) ?></td>
            </tr>
            <?php endforeach ?>
        </tbody>
    </table>
</section>

<section class="content code-container align-center">
    <div class="code center">
        <section class="content sample-code">
            <h2><?= $app->escape($i18n['db_code_title']) ?></h2>
            <pre><code class="language-php"><?= $app->escape($i18n['db_code']) ?></code></pre>           
        </section>
    </div>
</section>

<section class="content code-container align-center">
    <div class="code center">
        <section class="content sample-code">
            <h2><?= $app->escape($i18n['php_code_page']) ?></h2>
            <pre><code class="language-php"><?= $app->escape($controller_code) ?></code></pre>
        </section>
    </div>
</section>