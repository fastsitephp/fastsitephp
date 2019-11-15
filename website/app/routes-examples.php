<?php

// Define Example Routes
$app->get('/:lang/examples/request-demo', 'Examples\RequestDemo');
$app->get('/:lang/examples/response-demo', 'Examples\ResponseDemo');
$app->get('/:lang/examples/response/:type', 'Examples\ResponseDemo.byType');
$app->get('/:lang/examples/database-demo', 'Examples\DatabaseDemo');
$app->route('/:lang/examples/database-demo/:page', 'Examples\DatabaseDemo.routePage');
$app->get('/:lang/examples/email-demo', 'Examples\EmailDemo');
$app->post('/:lang/examples/email-demo/send-email', 'Examples\EmailDemo.sendEmail');
$app->post('/:lang/examples/email-demo/smtp-server', 'Examples\EmailDemo.smtpServer');
$app->route('/:lang/examples/l10n-demo', 'Examples\L10nDemo');
$app->get('/:lang/examples/encryption-demo', 'Examples\EncryptionDemo');
$app->get('/:lang/examples/encryption/generate-key', 'Examples\EncryptionDemo.generateKey');
$app->post('/:lang/examples/encryption/encrypt', 'Examples\EncryptionDemo.encrypt');
$app->post('/:lang/examples/encryption/decrypt', 'Examples\EncryptionDemo.decrypt');
