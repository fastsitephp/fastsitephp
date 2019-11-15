<style>
    section.content { max-width:800px; text-align:center; }
    .doc-list { margin:0 auto; text-align:left; display:inline-block; font-size:1.2em; list-style-type: none; }
    .doc-list li { margin: 10px auto; }
</style>

<section class="content">
    <h1><?= $app->escape($i18n['page_title']) ?></h1>
</section>

<section class="content">
    <ul class="doc-list">
        <?php foreach ($i18n['links'] as $link): ?>
            <li>
                <a href="<?= $app->rootUrl() . $app->lang ?>/examples/<?= $app->escape($link['page']) ?>">
                    <?= $app->escape($link['title']) ?>
                </a>
            </li>
        <?php endforeach ?>
    </ul>
</section>