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
        <img src="<?= $app->rootDir() ?>img/icons/Language.svg" alt="Language">
    </div>
    <div>
        <p><strong>Are you fluent in English and another language? If so then please get in touch.</strong></p>
        <p>I have two open source sites - this one FastSitePHP and <a href="https://www.dataformsjs.com/" target="_blank">DataFormsJS</a> and I will pay translators so they it can be translated into multiple languages.</p>
        <p>Most translations will occur in simple (*.json) files and be done page by page. This allows for core translations happen quickly without having to wait for the full site to be translated. Other translations will happen directly in code comments and markdown documents.</p>
        <p>Initially I’m looking for translators in the most widely used web and spoken languages. As time goes on more languages will be added however this is a personal project to start so I have to keep my costs affordable. For other languages I will consider them right now if the price is reasonable.</p>
        <p>Also to keep this affordable I’m not looking for professional firms so even if you have limited experience but are willing to translate then that is perfectly ok.</p>
        <p>My initial budget will be limited so depending on the average translator rate I may need only several hours per month per language.</p>
        <p>Before many languages are translated initial translations from Google Translate need to be verified.</p>
        <p>
            <ul class="bullet-list">
                <li><a href="https://www.fastsitephp.com/zh-Hans/" target="_blank">https://www.fastsitephp.com/zh-Hans/</a></li>
                <li><a href="https://www.dataformsjs.com/#/jp/" target="_blank">https://www.dataformsjs.com/#/jp/</a></li>
            </ul>
        </p>
        <p>If the translations from Google Translate or another AI/ML Service turn out to be mostly correctly then all initial language files will be created using a service. This is ideal otherwise it would take many years and a lot more money to have the site translated.</p>        
    </div>
    <div>
        <table>
            <thead>
                <tr><th>Language</th><th>Status</th></tr>
            </thead>
            <tbody>
                <tr><td>Arabic</td><td>Translator Needed in early 2020</td></tr>
                <tr class="highlight"><td>Chinese Simplified (zh-Hans)</td><td>Translator Needed Immediately</td></tr>
                <tr><td>Czech</td><td>Translator Needed in early 2020</td></tr>
                <tr><td>Dutch</td><td>Translator Needed in early 2020</td></tr>
                <tr><td>French</td><td>Translator Needed in early 2020</td></tr>
                <tr><td>German</td><td>Translator Needed in early 2020</td></tr>
                <tr><td>Greek</td><td>Translator Needed in early 2020</td></tr>
                <tr><td>Hindi</td><td>Translator Needed in early 2020</td></tr>
                <tr><td>Italian</td><td>Translator Needed in early 2020</td></tr>
                <tr class="highlight"><td>Japanese</td><td>Translator Needed Immediately</td></tr>
                <tr><td>Korean</td><td>Translator Needed in early 2020</td></tr>
                <tr><td>Persian</td><td>Translator Needed in early 2020</td></tr>
                <tr><td>Polish</td><td>Translator Needed in early 2020</td></tr>
                <tr><td>Portuguese</td><td>Translator Needed in early 2020</td></tr>
                <tr><td>Russian</td><td>Translator Needed in early 2020</td></tr>
                <tr><td>Spanish</td><td>Translator Needed in early 2020</td></tr>
                <tr><td>Turkish</td><td>Translator Needed in early 2020</td></tr>
                <tr><td>Vietnamese</td><td>Translator Needed in early 2020</td></tr>
            <tbody>
        </table>
    </div>
    <form method="POST">
        <input name="email" type="email" placeholder="Your Email" required value="<?= $app->escape($email_from) ?>">
        <textarea name="message" size="80" rows="10" required placeholder="Enter message..."><?= $app->escape($message) ?></textarea>
        <button type="submit">Submit</button>
    </form>
</section>
