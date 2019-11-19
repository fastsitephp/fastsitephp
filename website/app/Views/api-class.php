<style>
    .content a  { color:#3C456F; }
    .content a:hover { color:#8892BF; }
    section { max-width:1200px; }
    section h1,
    section h2 { margin-bottom:1em; }
    h1 { 
        font-size:32px;
        border-bottom: 2px solid #4F5B93;
        padding-bottom: 16px;
        border-top: 2px solid #4F5B93;
        padding-top: 16px;        
    }
    .flex { display:initial; max-width: 1500px; }
    .class-list {
        display: none;
        align-self: flex-start;
    }
    .class-info { 
        max-width: 880px; 
        padding-bottom: 20px; 
        align-self: flex-start;
    }
    .source-code,
    .related-links { margin-bottom:10px; }
    @media screen and (min-width: 1000px) {
        .flex { display:flex; }
        .class-list { display:inline-block; }
        .class-info { max-width:700px; }
    }
    @media screen and (min-width: 1200px) {
        .flex { justify-content: space-evenly; }
    }
    @media screen and (min-width: 1350px) {
        .class-info { max-width:880px; }
    }
    .class-info h2 {
        margin-top: 32px;
        padding-top: 16px;
        border-top: 2px solid #4F5B93;
        border-bottom: 2px solid #4F5B93;
        padding-bottom: 16px; 
    }
    .class-info h2.props,
    .class-info h2.methods { margin-top:96px; }
    .list-container { padding:20px; display:inline-block; border:2px solid #4F5B93; }
    .sample-code { margin: 40px auto; }
    .class-list ul { list-style-type:none; }
    .class-list li { padding: 4px 8px; }
    .class-list li.active { 
        background-color: #8892BF;
        background-image: linear-gradient(hsla(229, 30%, 69%, 1), #8892BF);
        border-radius: 4px;
    }
    .class-list li.active a,
    .class-list li.active a:hover { 
        color: white;
    }
    .func-attr { 
        margin-bottom: 16px;
        font-weight: bold;
    }
    .func-title {
        font-size: 16px;
        padding: 4px 8px;
        background-color: #dee0ed;
        background-image: linear-gradient(0deg, hsla(230, 30%, 85%, 1) 0%, hsla(230, 30%, 90%, 1) 100%);
        margin-top: 48px;
        margin-bottom: 16px;
    }
    .func-attr span {
        font-size: 14px;
        border-radius: 4px;
        padding: 4px 8px;
        background-color: #8892BF;
        background-image: linear-gradient(hsla(229, 30%, 69%, 1), #8892BF);
        color: white;
    }
    .func-title code { white-space:normal; }
    .func-desc { font-size:14px; }
    .table-props {
        border-collapse: collapse;
        margin-top: 48px;
    }
    .table-props thead {
        background-color: #dee0ed;
        background-image: linear-gradient(0deg, hsla(230, 30%, 85%, 1) 0%, hsla(230, 30%, 90%, 1) 100%);
        white-space:nowrap;
    }
    .table-props tbody tr {
        background-color: white;
    }
    .table-props tbody tr:nth-child(even) {
        background-color: hsla(232, 29%, 97%, 1);
    }
    .table-props th,
    .table-props td {
        padding: 4px 8px;
        border: 1px solid #4F5B93;
        vertical-align: top;
    }
    ul.link-list { margin-left: 2em; }
    ul.link-list li { line-height: 1.4em; }
</style>
<div class="flex">
    <section class="content class-list">
        <div class="list-container">
            <h2><?= $app->escape($i18n['class_list']) ?></h2>
            <ul>
                <?php foreach ($classes as $class_name): ?>
                    <li class="<?= ($class_name->name === $class->short_name ? 'active' : '') ?>">
                        <a href="<?= $app->rootUrl() . $app->lang ?>/api/<?= $app->escape($class_name->link) ?>">
                            <?= $app->escape($class_name->name) ?>
                        </a>
                    </li>
                <?php endforeach ?>
            </ul>
        </div>
    </section>
    <section class="content class-info">
        <h1><?= $app->escape($class->name) ?></h1>
        <p><?= $app->escapeDesc($class->description) ?></p>
        <h3 class="source-code"><?= $app->escape($i18n['source_code']) ?></h3>
        <p><a href="<?= $app->escape($class->github) ?>" target="_blank"><img src="../../img/logos/GitHub-Mark-32px.png" alt="GitHub" height="32" width="32"></a></p>
        <?php if ($class->links): ?>
            <h3 class="related-links"><?= $app->escape($i18n['related_links']) ?></h3>
            <ul class="link-list">
            <?php foreach ($class->links as $link): ?>                
                <li><a href="<?= $app->escape($link) ?>" target="_blank"><?= $app->escape($link) ?></a></li>
            <?php endforeach ?>
            </ul>
        <?php endif ?>

        <?php if ($example_code): ?>
            <h2><?= $app->escape($i18n['example_code']) ?></h2>
            <?php foreach ($example_code as $code): ?>
                <section class="content sample-code">
                    <?php if ($code->title): ?>
                        <h3><?= $app->escape($code->title) ?></h3>
                    <?php endif ?>
                    <pre><code class="language-php"><?= $app->escape($code->code) ?></code></pre>
                </section>
            <?php endforeach ?>
        <?php endif ?>

        <?php if ($class->properties): ?>
            <h2 class="props"><?= $app->escape($i18n['properties']) ?></h2>
            <table class="table-props">
                <thead>
                    <tr>
                        <th><?= $app->escape($i18n['name']) ?></th>
                        <th><?= $app->escape($i18n['data_type']) ?></th>
                        <th><?= $app->escape($i18n['default']) ?></th>
                        <th><?= $app->escape($i18n['description']) ?></th>
                    </tr>
                <thead>
                <tbody>
                <?php foreach ($class->properties as $prop): ?>
                    <tr id="<?= $prop->target ?>">
                        <td><?= $app->escape($prop->name) ?></td>
                        <td><?= $app->escapeDesc($prop->dataType) . ($prop->isStatic ? '<br><i>(' . $app->escape($i18n['static']) . ')</i>' : '') ?></td>
                        <td><?= $app->escape($prop->defaultValue) ?></td>
                        <td><?= $app->escapeDesc($prop->description) ?></td>
                    </tr>
                <?php endforeach ?>
                </tbody>
            </table>
        <?php endif ?>

        <?php if ($class->methods): ?>
            <h2 class="methods"><?= $app->escape($i18n['methods']) ?></h2>
            <?php foreach ($class->methods as $method): ?>
                <h3 class="func-title" id="<?= $method->target ?>"><code><?= $app->escape($method->definition) ?></code></h3>
                <?php if ($method->isStatic || $method->isGetterSetter): ?>
                    <div class="func-attr">
                        <?php if ($method->isStatic): ?><span><?= $app->escape($i18n['static_func']) ?></span><?php endif ?>
                        <?php if ($method->isGetterSetter): ?><span><?= $app->escape($i18n['getter_setter']) ?></span><?php endif ?>
                    </div>
                <?php endif ?>
                <p class="func-desc"><?= $app->escapeDesc($method->description) ?></p>
                <?php if ($method->returnType): ?>
                    <p class="func-desc"><strong><?= $app->escape($i18n['returns']) ?>:</strong> <?= $app->escape($method->returnType) ?></p>
                <?php endif ?>
                <?php if ($method->links): ?>
                    <ul class="link-list">
                    <?php foreach ($method->links as $link): ?>                
                        <li><a href="<?= $app->escape($link) ?>" target="_blank"><?= $app->escape($link) ?></a></li>
                    <?php endforeach ?>
                    </ul>
                <?php endif ?>                
            <?php endforeach ?>
        <?php endif ?>
    </section>
</div>