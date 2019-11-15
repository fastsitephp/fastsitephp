<style>
    form { text-align:center; margin:40px auto; }
    input { margin-right:20px; }
    .info { max-width:700px; margin:auto; }
</style>
<div>
    <section class="content example-title inline-block center align-center">
        <h1><?= $app->escape($i18n['page_title']) ?></h1>
        <div class="api-link">
            <?php if (isset($app->config['ExamplesSite'])): ?>
                <a href="https://www.fastsitephp.com/<?= $app->lang ?>/api/Lang_L10N" target="_blank"><?= $app->escape($i18n['api_docs']) ?></a>
            <?php else: ?>
                <a href="<?= $app->rootUrl() . $app->lang ?>/api/Lang_L10N"><?= $app->escape($i18n['api_docs']) ?></a>
            <?php endif ?>
        </div>
    </section>
</div>
<section class="content inline-block center">
    <div class="info">
        <p><?= $app->escape($i18n['info_1']) ?></p>
        <p><?= $app->escape($i18n['info_2']) ?></p>
    </div>
    <form method="POST">
        <input name="datetime-local" type="datetime-local" required value="<?= $app->escape($date) ?>">
        <input name="number" type="number" required value="<?= $app->escape($number) ?>" step="any">
        <button type="submit"><?= $app->escape($i18n['update_table']) ?></button>
    </form>
    <section class="content sample-code">
        <h2><?= $app->escape($i18n['example_code']) ?></h2>
        <pre><code class="language-php"><?= $app->escape($code) ?></code></pre>
    </section>
    <table id="date-time-table">
        <thead>
            <tr>
                <th><?= $app->escape($i18n['locale']) ?></th>
                <th><?= $app->escape($i18n['number']) ?></th>
                <th><?= $app->escape($i18n['date_time']) ?></th>
                <th><?= $app->escape($i18n['date']) ?></th>
                <th><?= $app->escape($i18n['time']) ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($records as $record): ?>
            <tr>
                <?php for ($n = 0; $n < 5; $n++): ?>
                    <td><?php echo $app->escape($record[$n]) ?></td>
                <?php endfor ?>
            </tr>
            <?php endforeach ?>
        </tbody>
    </table>
</section>