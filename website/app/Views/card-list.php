<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Tangerine">
<style>
    /* Card Layout */
    .card-container { 
        max-width: calc(340px * 3);
        text-align: center;
        margin: 0 auto;
    }
    ul.card-list {
        display: flex;
        flex-direction: row;
        flex-wrap: wrap;
        margin: 0 auto;
        justify-content: center;
    }
    ul.card-list li {
        display: block;
        background-color: #fff;
        margin: 20px 20px 40px;
        padding: 0;
        box-shadow: 0 1px 5px 0 rgba(0,0,0,.5);
        border-radius: 4px;
        width: 300px;
        transition: all .3s ease-in-out;
    }
    ul.card-list li .img,
    ul.card-list li .category,
    ul.card-list li .text {
        display: block;
        transition: all .3s ease-in-out;
    }
    ul.card-list li .img {
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        background-color: #ffbc91;
        background-image: url('../img/card-background.svg');
        background-size: cover;
        border-top-left-radius: 4px;
        border-top-right-radius: 4px;
        background-position-y: center;
        height: 120px;
        border-bottom: 1px solid hsla(23, 100%, 43%, 1);
    }
    ul.card-list li .category {
        padding: 1em 30px 0.5em 30px;
        text-align: left;
        font-size: 1.5em;
        text-transform: uppercase;
    }
    ul.card-list li .text {
        padding: 15px 30px 0 30px;
        min-height: 120px;
        text-align: left;
        font-size: 1.2em;
        color: #8892BF;
    }
    ul.card-list li a {
        text-decoration:none;
    }
    ul.card-list li img {
        background-color: transparent;
        height: 60px;
    }
    ul.card-list li .img-circle {
        background-color: white;
        padding: 20px;
        height: 60px;
        min-width: 60px;
        box-shadow: 0 0 1px hsla(23, 100%, 40%, 0.5),
                    inset 0 0 2px 1px hsla(23, 100%, 30%, 0.8),
                    inset 0 0 4px 2px hsla(23, 100%, 40%, 0.8);
        transition: all .3s ease-in-out;
        border-radius: 50%;
    }

    /* Update Specific Images - Generally needed if they are wider than they are tall, prefect square, or have points that stick out */
    ul.card-list li img[src$='/IBM_logo.svg'] { height:40px; margin-top:10px; }
    ul.card-list li img[src$='/Code-Editor.svg'] { height:50px; margin-top:5px; }
    ul.card-list li img[src$='/Windows_logo_-_2012.svg'] { height:50px; margin-top:5px; }
    ul.card-list li img[src$='/apple.svg'] { margin-top:-2px; }

    /* Card Hover Animations */
    ul.card-list li:hover {
        background-color: hsla(229, 30%, 74%, 1);
        box-shadow: 0 0 40px 0 rgba(0,0,0,.5);
    }
    ul.card-list li:hover .img {
        background-image: url('../img/card-background2.svg');
        border-bottom: 1px solid #4F5B93;
        height: 140px;
    }
    ul.card-list li:hover .img-circle {
        box-shadow: 0 0 1px hsla(23, 100%, 40%, 0.5),
                    inset 0 0 6px 3px hsla(23, 100%, 30%, 0.8),
                    inset 0 0 12px 6px hsla(23, 100%, 40%, 0.8);
    }
    ul.card-list li:hover .category {
        background-color: #8892BF;
        color: white;
        text-shadow: 1px 1px 2px hsla(229, 30%, 24%, 1);
        text-transform: none;
        font-family: 'Tangerine', Georgia, 'Times New Roman', Times, serif;
        font-size: 44px;
        padding: .25em 30px;
    }
    ul.card-list li:hover .text {
        color: white;
        text-shadow: 1px 1px 2px hsla(229, 30%, 24%, 1);
        min-height: 85px;
    }
</style>

<div>
    <section class="content page-title">
        <h1><?= $app->escape($i18n['page_title']) ?></h1>
    </section>
</div>

<div class="card-container">
    <ul class="card-list">
        <?php foreach ($i18n['links'] as $link): ?>
        <li>
            <a href="<?= $app->rootUrl() . $app->lang ?>/<?= $nav_active_link ?>/<?= $app->escape($link['page']) ?>">
                <span class="img">
                    <span class="img-circle">
                        <?php if (isset($link['img'])): ?>
                            <img src="<?= $app->escape($link['img']) ?>" alt="<?= $app->escape($link['img_alt']) ?>">
                        <?php endif ?>
                    </span>
                </span>
                <span class="category">
                    <?= $app->escape($link['category']) ?>
                </span>
                <span class="text">
                    <?= $app->escape($link['title']) ?>
                </span>
            </a>
        </li>
        <?php endforeach ?>
    </ul>
</div>