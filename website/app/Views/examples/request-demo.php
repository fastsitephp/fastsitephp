<style>
    .tab-content { overflow-y: scroll; }
    .nowrap { white-space:nowrap; }
    section.content { width:90%; max-width:1050px; padding:0; }
    section.content.sample-code { max-width:700px; margin-top:40px; padding:20px; }
    section.content.inline-block { min-width:600px; }
    .title,
    .request-headers p { text-align:center; }
</style>

<div>
    <section class="content example-title center align-center">
        <h1><?= $app->escape($i18n['page_title']) ?></h1>
        <?php if (isset($app->config['ExamplesSite'])): ?>
            <a href="https://www.fastsitephp.com/<?= $app->lang ?>/api/Web_Request" target="_blank"><?= $app->escape($i18n['api_docs']) ?></a>
        <?php else: ?>
            <a href="<?= $app->rootUrl() . $app->lang ?>/api/Web_Request"><?= $app->escape($i18n['api_docs']) ?></a>
        <?php endif ?>
    </section>
</div>

<section class="content inline-block center">
    <div class="tabs">
        <span class="tab active" data-target=".request-headers"><?= $app->escape($i18n['request_headers']) ?></span>
        <span class="tab" data-target=".app-props"><?= $app->escape($i18n['app_props']) ?></span>
        <span class="tab" data-target=".req-props"><?= $app->escape($i18n['req_props']) ?></span>
        <span class="tab" data-target=".req-content"><?= $app->escape($i18n['req_content']) ?></span>
    </div>


    <div class="tab-content request-headers">
        <p><?= $app->escape($i18n['request_headers_desc']) ?></p>
        <table>
            <thead>
                <tr>
                    <th><?= $app->escape($i18n['header']) ?></th>
                    <th><?= $app->escape($i18n['value']) ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($headers as $key => $value): ?>
                <tr>
                    <td class="nowrap"><?= $app->escape($key) ?></td>
                    <td><?= nl2br($app->escape($value)) ?></td>
                </tr>
                <?php endforeach ?>
            </tbody>
        </table>
        <section class="content sample-code">
            <h2><?= $app->escape($i18n['example_code']) ?></h2>
            <pre><code class="language-php"><?= $app->escape($i18n['header_code']) ?></code></pre></code></pre>
        </section>
    </div>


    <div class="tab-content app-props align-center" style="display:none;">
        <p class="align-left"><?= $app->escape($i18n['app_props_desc']) ?></p>
        <table class="center">
            <thead>
                <tr>
                    <th><?= $app->escape($i18n['function']) ?></th>
                    <th><?= $app->escape($i18n['returns']) ?></th>
                    <th><?= $app->escape($i18n['value']) ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($app_props as $item): ?>
                <tr>
                    <td class="nowrap"><?= $app->escape($item[0]) ?></td>
                    <td class="nowrap"><?= $app->escape($item[1]) ?></td>
                    <td><?= $app->escape($item[2]) ?></td>
                </tr>
                <?php endforeach ?>  
            </tbody>
        </table>
    </div>


    <div class="tab-content req-props align-center" style="display:none;">
        <p class="align-left"><?= $app->escape($i18n['req_props_desc']) ?></p>
        <table class="center">
            <thead>
                <tr>
                    <th><?= $app->escape($i18n['function']) ?></th>
                    <th><?= $app->escape($i18n['returns']) ?></th>
                    <th><?= $app->escape($i18n['proxy']) ?></th>
                    <th><?= $app->escape($i18n['value']) ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($req_props as $item): ?>
                <tr>
                    <td class="nowrap"><?= $app->escape($item[0]) ?></td>
                    <td class="nowrap"><?= $app->escape($item[1]) ?></td>
                    <td class="align-center"><?= ($item[2] === 1 ? '&#10003;' : '') ?></td>
                    <td><?= $app->escape($item[3]) ?></td>
                </tr>
                <?php endforeach ?>  
            </tbody>
        </table>
        <section class="content sample-code">
            <h2><?= $app->escape($i18n['example_code']) ?></h2>
            <pre><code class="language-php"><?= $app->escape($i18n['req_code']) ?></code></pre></code></pre>
        </section>
    </div>


    <div class="tab-content req-content align-center" style="display:none;">
        <p class="align-left"><?= $app->escape($i18n['req_content_desc']) ?></p>
        <table class="center">
            <thead>
                <tr>
                    <th><?= $app->escape($i18n['function']) ?></th>
                    <th><?= $app->escape($i18n['returns']) ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($req_content as $item): ?>
                <tr>
                    <td class="nowrap"><?= $app->escape($item[0]) ?></td>
                    <td class="nowrap"><?= $app->escape($item[1]) ?></td>
                </tr>
                <?php endforeach ?>  
            </tbody>
        </table>
    </div>
</section>
