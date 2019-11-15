<h1><?= $app->escape($page_title) ?></h1>
<?php if (count($list) === 0): ?>
    <p>No Records found</p>
<?php else: ?>
    <ol>
        <?php foreach ($list as $item): ?>
            <li><?= $app->escape($item) ?></li>
        <?php endforeach ?>
    </ol>
<?php endif ?>
<p><?= $app->escape($year) ?></p>