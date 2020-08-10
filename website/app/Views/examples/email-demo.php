<style>
    section.content.tab-container { width:90%; min-width:600px; max-width:1050px; padding:0; }

    form { width:100%; max-width:700px; }
    .form-row { margin-bottom:20px; text-align:left; display:flex; }
    .form-row label { width:140px; display:inline-block; font-weight:bold; }
    form ul { margin-bottom:20px; }

    /*
    NOTE - Radio [content:attr(value)] works but may not be easy to read 
    for accessibility or for screen readers. For ideal accessibility a <label>
    should be used with each radio control.
    */
    input[type='text'],
    input[type='email'],
    input[type='password'] {
        display: inline-block;
        width: calc(100% - 176px);
        border-radius: 2em;
        border: none;
        background-color: #E2E4EF;
        outline: none;
    }
    input[type='radio'] {
        height: 24px;
        width: 24px;
        margin: 0;
        margin-right: 70px;
        position: relative;
    }
    input[type='radio']::after {
        content: attr(value);
        color: #4F5B93;
        margin-left: 40px;
        top: 5px;
        position: absolute;
    }
    input[name='smtp-other-port'] {
        width: 4em;
        display: inline;
        padding: 2px 4px;
        margin-left: 20px;
    }
    input[name='smtp-timeout'] { width:3em; margin-right:10px; padding: 4px 8px; }

    select { margin-right:10px; }
    .flex { display:flex; justify-content: center; align-items: center; }
    .loading-container { 
        background-color:#8892BF;
        height: 54px;
        display: flex;
        align-items: flex-end;
    }
    .loading-container.email { margin-left:10px; } 

    hr { background-color:#4F5B93; width:100%; height:2px; border:none; margin-bottom:20px; }
    h2 { margin-top:20px; padding-top:20px; border-top:solid 2px #4F5B93; }
    .tab-content.info h2 { border-top:none; margin:0; padding:0; margin-bottom:20px; text-align:left; }
    .tab-content.info .content { max-width:700px; }
    .tab-content.info .content:last-child { margin-bottom: 0; }

    #fields-warning li {
        background-color: red;
        color: white;
        padding: 5px 20px;
        margin-bottom: 10px;
        border-radius: 2em;
        box-shadow: 0 1px 1px 0 rgba(0,0,0,.5);    
    }
    #fields-warning li:last-child { margin-bottom:0; }

    #smtp-server-log,
    #email-server-log { text-align: left; overflow-x: scroll;}

    .local-download {
        border: 1px solid hsla(23, 100%, 43%, 1);
        background-color: #FFAB76;
        padding: 10px 20px;
        color: white;
        font-weight: bold;
    }
    
    .smtp-info { margin-top:40px; margin-bottom:40px; }

    /* HTML Editor Control */
    .editor-container { resize: both; overflow: auto; height:300px; flex-direction:column; }
    .editor-toolbar { display: flex; }
    .editor-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        background-color: #efefef;
        border: 1px solid rgb(169, 169, 169);
        border-right: none;
        border-bottom: none;
    }
    .editor-btn:last-child { border-right: 1px solid rgb(169, 169, 169); }
    .editor-btn:hover {
        cursor: pointer;
        background-color: #d6d6d6;
        user-select: none;
    }
    .editor-btn.active {
        background-color: #d6d6d6;
    }
    .editor-btn[data-command="bold"] { font-weight:bold; }
    .editor-btn[data-command="italic"] { font-style: italic; }
    .editor-btn[data-command="insertUnorderedList"] {
        background-image: url("data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzIiIGhlaWdodD0iMzIiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CiA8IS0tIENyZWF0ZWQgd2l0aCBNZXRob2QgRHJhdyAtIGh0dHA6Ly9naXRodWIuY29tL2R1b3BpeGVsL01ldGhvZC1EcmF3LyAtLT4KIDxnPgogIDx0aXRsZT5iYWNrZ3JvdW5kPC90aXRsZT4KICA8cmVjdCBmaWxsPSJub25lIiBpZD0iY2FudmFzX2JhY2tncm91bmQiIGhlaWdodD0iMzQiIHdpZHRoPSIzNCIgeT0iLTEiIHg9Ii0xIi8+CiAgPGcgZGlzcGxheT0ibm9uZSIgb3ZlcmZsb3c9InZpc2libGUiIHk9IjAiIHg9IjAiIGhlaWdodD0iMTAwJSIgd2lkdGg9IjEwMCUiIGlkPSJjYW52YXNHcmlkIj4KICAgPHJlY3QgZmlsbD0idXJsKCNncmlkcGF0dGVybikiIHN0cm9rZS13aWR0aD0iMCIgeT0iMCIgeD0iMCIgaGVpZ2h0PSIxMDAlIiB3aWR0aD0iMTAwJSIvPgogIDwvZz4KIDwvZz4KIDxnPgogIDx0aXRsZT5MYXllciAxPC90aXRsZT4KICA8ZWxsaXBzZSByeT0iMiIgcng9IjIiIGlkPSJzdmdfMiIgY3k9IjcuNTI3NzgiIGN4PSI3LjgwNTU1IiBzdHJva2U9IiMwMDAiIGZpbGw9IiMwMDAwMDAiLz4KICA8bGluZSBzdHJva2UtbGluZWNhcD0idW5kZWZpbmVkIiBzdHJva2UtbGluZWpvaW49InVuZGVmaW5lZCIgaWQ9InN2Z18zIiB5Mj0iNy40NTgzNCIgeDI9IjI3LjU1NjAzIiB5MT0iNy40NTgzNCIgeDE9IjEzLjI5MTY2IiBzdHJva2U9IiMwMDAiIGZpbGw9Im5vbmUiLz4KICA8ZWxsaXBzZSByeT0iMiIgcng9IjIiIGlkPSJzdmdfMTAiIGN5PSIxNS40NzQ4NSIgY3g9IjcuODA1NTUiIHN0cm9rZT0iIzAwMCIgZmlsbD0iIzAwMDAwMCIvPgogIDxsaW5lIHN0cm9rZS1saW5lY2FwPSJ1bmRlZmluZWQiIHN0cm9rZS1saW5lam9pbj0idW5kZWZpbmVkIiBpZD0ic3ZnXzExIiB5Mj0iMTUuNDA1NCIgeDI9IjI3LjU1NjAzIiB5MT0iMTUuNDA1NCIgeDE9IjEzLjI5MTY2IiBzdHJva2U9IiMwMDAiIGZpbGw9Im5vbmUiLz4KICA8ZWxsaXBzZSByeT0iMiIgcng9IjIiIGlkPSJzdmdfMTIiIGN5PSIyMy4yNTIzIiBjeD0iNy44MDU1NSIgc3Ryb2tlPSIjMDAwIiBmaWxsPSIjMDAwMDAwIi8+CiAgPGxpbmUgc3Ryb2tlLWxpbmVjYXA9InVuZGVmaW5lZCIgc3Ryb2tlLWxpbmVqb2luPSJ1bmRlZmluZWQiIGlkPSJzdmdfMTMiIHkyPSIyMy4xODI4NiIgeDI9IjI3LjU1NjAzIiB5MT0iMjMuMTgyODYiIHgxPSIxMy4yOTE2NiIgc3Ryb2tlPSIjMDAwIiBmaWxsPSJub25lIi8+CiA8L2c+Cjwvc3ZnPg==");
    }
    [contenteditable] { 
        border: 1px solid rgb(169, 169, 169);
        padding: 16px;
        margin: 0;
        width: calc(100% - 34px);
        height: calc(100% - 34px);
        color: black;
    }
    [contenteditable] * {
        padding: initial;
        margin: initial;
    }
    [contenteditable] ul {
        margin-left: 2em;
    }
</style>

<div>
    <section class="content example-title center align-center">
        <h1><?= $app->escape($i18n['page_title']) ?></h1>
        <?php if (isset($app->config['ExamplesSite'])): ?>
            <a href="https://www.fastsitephp.com/<?= $app->lang ?>/api/Net_SmtpClient" target="_blank" rel="noopener"><?= $app->escape($i18n['api_docs']) ?></a>
        <?php else: ?>
            <a href="<?= $app->rootUrl() . $app->lang ?>/api/Net_SmtpClient"><?= $app->escape($i18n['api_docs']) ?></a>
        <?php endif ?>
    </section>
</div>

<section class="content tab-container align-center">
    <div class="tabs">
        <span class="tab active" data-target=".send-email"><?= $app->escape($i18n['send_email']) ?></span>
        <span class="tab" data-target=".smtp-commands"><?= $app->escape($i18n['smtp_commands']) ?></span>
        <span class="tab" data-target=".info"><?= $app->escape($i18n['info']) ?></span>
        <span class="tab" data-target=".code"><?= $app->escape($i18n['example_code']) ?></span>
    </div>
    <div class="tab-content send-email">
        <form class="center">
            <ul class="bullet-list">
                <li><?= $app->escape($i18n['email_info_1']) ?></li>
                <li><?= $app->escape($i18n['email_info_2']) ?></li>
                <li><?= $app->escape($i18n['email_info_3']) ?></li>
                <li><?= $app->escape($i18n['email_info_4']) ?></li>
            </ul>

            <hr>
            <div class="form-row">
                <label for="smtp-host"><?= $app->escape($i18n['smtp_host']) ?>:</label>
                <input type="text" id="smtp-host" name="smtp-host" placeholder="<?= $app->escape($i18n['smtp_host']) ?>" autocomplete="on">
            </div>
            <div class="form-row">
                <label><?= $app->escape($i18n['smtp_port']) ?>:</label>
                <input type="radio" name="smtp-port" id="smtp-port-587" value="587" checked>
                <input type="radio" name="smtp-port" id="smtp-port-25" value="25">
                <input type="radio" name="smtp-port" value="Other">
                <input type="number" name="smtp-other-port" value="465" disabled>
            </div>
            <div class="form-row">
                <label for="smtp-timeout"><?= $app->escape($i18n['smtp_timeout']) ?>:</label>
                <input type="number" id="smtp-timeout" name="smtp-timeout" value="5"> <span><?= $app->escape($i18n['seconds']) ?></span>
            </div>
            <div class="form-row">
                <label for="smtp-user"><?= $app->escape($i18n['smtp_user']) ?>:</label>
                <input type="text" id="smtp-user" name="smtp-user" autocomplete="username">
            </div>
            <div class="form-row">
                <label for="smtp-host"><?= $app->escape($i18n['smtp_password']) ?>:</label>
                <input type="password" id="smtp-password" name="smtp-password" autocomplete="current-password">
            </div>

            <hr>
            <div class="form-row">
                <label for="email-from"><?= $app->escape($i18n['email_from']) ?>:</label>
                <input type="email" id="email-from" name="email-from">
            </div>
            <div class="form-row">
                <label for="email-to"><?= $app->escape($i18n['email_to']) ?>:</label>
                <input type="email" id="email-to" name="email-to">
            </div>
            <div class="form-row">
                <label for="email-subject"><?= $app->escape($i18n['email_subject']) ?>:</label>
                <input type="text" id="email-subject" name="email-subject">
            </div>
            <div class="form-row editor-container">
                <div class="editor-toolbar">
                    <span class="editor-btn" data-command="bold">B</span>
                    <span class="editor-btn" data-command="italic">I</span>
                    <span class="editor-btn" data-command="insertUnorderedList"></span>
                </div>
                <div id="email-body" contenteditable></div>
            </div>

            <hr>            
            <?php if ($is_local): ?>
                <div class="align-center">
                    <ul id="fields-warning" class="center inline-block mb20" style="display:none;">
                        <li id="warning-host"><?= $app->escape($i18n['enter_smtp_host']) ?></li>
                        <li id="warning-from"><?= $app->escape($i18n['enter_email_from']) ?></li>
                        <li id="warning-to"><?= $app->escape($i18n['enter_email_to']) ?></li>
                    </ul>
                </div>
                <div class="flex">
                    <button type="button" id="btn-send-email" disabled><?= $app->escape($i18n['send_email']) ?></button>
                    <div class="email loading-container" style="display:none;">
                        <div class="la-ball-climbing-dot">
                            <div></div>
                            <div></div>
                            <div></div>
                            <div></div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="local-download">
                    <?= $app->escape($i18n['local_download']) ?>
                </div>
            <?php endif ?>            
        </form>

        <h2 id="email-sent-label" style="display:none;"><?= $app->escape($i18n['email_sent']) ?></h2>
        <h2 id="email-error-label" style="display:none;"><?= $app->escape($i18n['email_error']) ?></h2>
        <pre id="email-server-log" style="display:none;"></pre>
    </div>
    <div class="tab-content smtp-commands" style="display:none;">
        <div class="flex">
            <select id="smtp-server">
                <option value=""><?= $app->escape($i18n['select_server']) ?></option>
                <option value="gmail">Google Gmail</option>
                <option value="live">Outlook.com (Hotmail)</option>
                <option value="aws">Amazon AWS</option>
            </select>
            <div class="smtp loading-container" style="display:none;">
                <div class="la-ball-climbing-dot">
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                </div>
            </div>
        </div>
        <pre id="smtp-server-log" style="display:none;"></pre>
        <div class="smtp-info">
            <ul class="bullet-list">
                <li><?= $app->escape($i18n['smtp_info_1']) ?></li>
                <li><?= $app->escape($i18n['smtp_info_2']) ?></li>
                <li><?= $app->escape($i18n['smtp_info_3']) ?></li>
                <li><?= $app->escape($i18n['smtp_info_4']) ?></li>
            </ul>
        </div>
        <section class="content sample-code">
            <pre><code class="language-php"><?= $app->escape($i18n['smtp_code']) ?></code></pre>
        </section>        
    </div>
    <div class="tab-content info" style="display:none;">
        <section class="content">
            <h2><?= $app->escape($i18n['why_use']) ?></h2>
            <ul class="bullet-list">
                <li><?= $app->escape($i18n['info_1']) ?></li>
                <li><?= $app->escape($i18n['info_2']) ?></li>
                <li><?= $app->escape($i18n['info_3']) ?></li>
                <li><?= $app->escape($i18n['info_4']) ?></li>
                <li><?= $app->escape($i18n['info_5']) ?></li>
                <li><?= $app->escape($i18n['info_6']) ?></li>
                <li><?= $app->escape($i18n['info_7']) ?></li>
            </ul>
        </section>
        
        <section class="content">
            <h2><?= $app->escape($i18n['other_clients']) ?></h2>
            <p><?= $app->escape($i18n['info_other']) ?></p>
            <ul class="bullet-list">
                <li><a href="https://github.com/PHPMailer/PHPMailer" target="_blank" rel="noopener">https://github.com/PHPMailer/PHPMailer</a></li>
                <li><a href="https://swiftmailer.symfony.com/" target="_blank" rel="noopener">https://swiftmailer.symfony.com/</a></li>
                <li><a href="https://zendframework.github.io/zend-mail/" target="_blank" rel="noopener">https://zendframework.github.io/zend-mail/</a></li>
            </ul>
        </section>

        <section class="content">
            <h2><?= $app->escape($i18n['other_lang']) ?></h2>
            <ul class="bullet-list">
                <li><strong>JavaScript (Node)</strong> <a href="https://nodemailer.com/about/" target="_blank" rel="noopener">https://nodemailer.com/about/</a></li>
                <li><strong>Python</strong> <a href="https://docs.python.org/3/library/smtplib.html" target="_blank" rel="noopener">https://docs.python.org/3/library/smtplib.html</a></li>
                <li><strong>Ruby Mail</strong> <a href="https://rubygems.org/gems/mail/" target="_blank" rel="noopener">https://rubygems.org/gems/mail/</a></li>
                <li><strong>Ruby SMTP</strong> <a href="https://ruby-doc.org/stdlib/libdoc/net/smtp/rdoc/Net/SMTP.html" target="_blank" rel="noopener">https://ruby-doc.org/stdlib/libdoc/net/smtp/rdoc/Net/SMTP.html</a></li>
                <li><strong>C# / .Net</strong> <a href="https://docs.microsoft.com/en-us/dotnet/api/system.net.mail" target="_blank" rel="noopener">https://docs.microsoft.com/en-us/dotnet/api/system.net.mail</a></li>
                <li><strong>Go Lang</strong> <a href="https://golang.org/pkg/net/smtp/" target="_blank" rel="noopener">https://golang.org/pkg/net/smtp/</a></li>
            </ul>
        </section>
    </div>
    <div class="tab-content code" style="display:none;">
        <section class="content sample-code">
            <pre><code class="language-php"><?= $app->escape($i18n['code']) ?></code></pre>
        </section>
    </div>
</section>
