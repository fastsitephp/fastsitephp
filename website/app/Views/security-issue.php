<style>
    .content.form {
        text-align: center;
        max-width: 800px;
        margin-bottom: 80px;
    }
    form {
        display: flex;
        flex-direction: column;
        align-items: center;
        margin-top: 20px;
    }
    input, textarea { margin-bottom:20px; width:100%; max-width:400px; }
    .error {
        display: inline-block;
        padding-left: 2em;
        margin-top: 20px;
        margin-bottom: 40px;
    }
    .message-sent { margin-top: 20px; }
    .message-sent span { 
        background-color: #4F5B93;
        display: inline-block;
        color: white;
        padding: 1em;
        margin-bottom: 40px;
    }
    table tbody tr.highlight { background-color:yellow; }
</style>

<div>
    <section class="content page-title">
        <h1><?= $app->escape($i18n['page_title']) ?></h1>
    </section>
</div>
<section class="content form">
    <?php if ($message_sent): ?>
        <div class="message-sent">
            <span><?= $app->escape($sent_info) ?></span>
        </div>
    <?php elseif ($errors): ?>
        <ul class="error bullet-list">
        <?php foreach ($errors as $error): ?>
            <li><?= $app->escape($error) ?></li>
        <?php endforeach ?>
        </ul>
    <?php endif ?>
    <div style="margin-bottom:20px;">
        <img src="<?= $app->rootDir() ?>img/icons/Security-Lock.svg" alt="<?= $app->escape($i18n['security']) ?>">
    </div>
    <div style="padding:40px;">
        <p style="font-size:2em;">
            This form has been disabled for security because the playground server now runs on the same server as the main server.
            If you need to contact the author of FastSitePHP or DataFormsJS please contact the author
            (Conrad) on social media from a link on his main site <a href="https://conradsollitt.com/" target="_blank" rel="noopener">https://conradsollitt.com/</a>.
        </p>
    </div>
    <div>
        <p><?= $app->escape($i18n['security_issue']) ?></p>
        <p>
        <a href="https://www.fastsitephp.com/en/playground" target="_blank" rel="noopener">https://www.fastsitephp.com/en/playground</a>
        </p>
        <p>
        <a href="https://www.dataformsjs.com/en/playground" target="_blank" rel="noopener">https://www.dataformsjs.com/en/playground</a>
        </p>
    </div>
    <form method="POST">
        <input name="email" type="email" placeholder="Your Email" disabled required value="<?= $app->escape($email_from) ?>">
        <textarea name="message" size="80" rows="10" disabled required placeholder="Enter message..."><?= $app->escape($message) ?></textarea>
        <button disabled type="submit"><?= $app->escape($i18n['submit']) ?></button>
    </form>
</section>
