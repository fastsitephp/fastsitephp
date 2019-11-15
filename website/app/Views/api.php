<style>
    .content { padding: 0; }
    .content a  { color:#3C456F; }
    .content a:hover { color:#8892BF; }
    section { max-width:800px; }
    section h3 { margin-top:1em; }
    section ul { list-style-type:none; }
    section li { margin: 10px auto; }
    .classes { text-align:center; }
    .class-list { margin:40px auto 0 auto; text-align:left; display:inline-block; font-size:1.2em; }
    .class { margin-top: 40px; padding-top:40px; border-top: 2px solid #4F5B93; }
    .filter { margin:0; width:100%; max-width:calc(100% - 40px); margin-bottom:20px; }
    @media (min-width:800px) {
        .filter { margin:20px; max-width:600px; }
    }
    .filter-info { font-weight:bold; text-align:center; }    
    .icon { 
        margin-right: 1em;
        padding: .2em .5em .4em;
        border: 1px solid hsla(23, 100%, 43%, 1);
        background-color: #FFAB76;
        color: hsla(23, 100%, 43%, 1);
        white-space:nowrap;
        display:inline-block;
    }
    .tab.active .icon { color: white; }
    .icon-class::after { content:"{ }"; }
    .icon-fn::after { content:"( )"; }
</style>

<div>
    <section class="content page-title">
        <h1><?= $app->escape($i18n['page_title']) ?></h1>
    </section>
</div>

<section class="content">
    <div class="tabs">
        <span class="tab active" data-target=".classes"><span class="icon icon-class"></span><?= $app->escape($i18n['class_list']) ?></span>
        <span class="tab" data-target=".functions"><span class="icon icon-fn"></span><?= $app->escape($i18n['properties_methods']) ?></span>
    </div>
    <div class="tab-content classes">
        <input class="filter" 
            disabled
            data-filter=".class-list li"
            data-label=".filter-info-classes"
            data-placeholder="<?= $app->escape($i18n['filter_placeholder']) ?>"
            data-disabled="<?= $app->escape($i18n['filter_disabled']) ?>">
        <div class="filter-info-classes" 
            data-text-all="<?= $app->escape($i18n['filter_all']) ?>"
            data-text-filtered="<?= $app->escape($i18n['filter_filtered']) ?>">
            <?= str_replace('{count}', count($classes), $app->escape($i18n['filter_all'])) ?>
        </div>
        <ul class="class-list">
            <?php foreach ($classes as $class_name): ?>
                <li>
                    <a href="<?= $app->rootUrl() . $app->lang ?>/api/<?= $app->escape($class_name->link) ?>">
                        <?= $app->escape($class_name->name) ?>
                    </a>
                </li>
            <?php endforeach ?>
        </ul>
    </div>
    <div class="tab-content functions" style="display:none;">
        <input class="filter" 
            disabled
            data-filter=".class"
            data-label=".filter-info-functions"
            data-placeholder="<?= $app->escape($i18n['filter_placeholder']) ?>"
            data-disabled="<?= $app->escape($i18n['filter_disabled']) ?>">
        <div class="filter-info-functions" 
            data-text-all="<?= $app->escape($i18n['filter_all']) ?>"
            data-text-filtered="<?= $app->escape($i18n['filter_filtered']) ?>">
            <?= str_replace('{count}', count($classes), $app->escape($i18n['filter_all'])) ?>
        </div>
        <?php foreach ($classes as $class): ?>
            <div class="class">
                <h2><a href="<?= $app->rootUrl() . $app->lang ?>/api/<?= $class->link ?>"><?= $app->escape($class->name) ?></a></h2>
                <?php if ($class->properties): ?>
                    <h3><?= $app->escape($i18n['properties']) ?></h3>
                    <ul>
                        <?php foreach ($class->properties as $prop): ?>
                            <li><a href="<?= $app->rootUrl() . $app->lang ?>/api/<?= $prop->link ?>"><?= $app->escape($prop->name) ?></a></li>
                        <?php endforeach ?>
                    </ul>
                <?php endif ?>
                <?php if ($class->methods): ?>
                    <h3><?= $app->escape($i18n['methods']) ?></h3>
                    <ul>
                        <?php foreach ($class->methods as $method): ?>
                            <li><a href="<?= $app->rootUrl() . $app->lang ?>/api/<?= $method->link ?>"><?= $app->escape($method->name) ?></a></li>
                        <?php endforeach ?>
                    </ul>
                <?php endif ?>
            </div>
        <?php endforeach ?>
    </div>
</section>
