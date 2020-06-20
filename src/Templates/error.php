<?php

/**
 * @var \FastSitePHP\Application $app
 * @var string $page_title
 * @var string $message
 * @var \ErrorException|null $e
 */

?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $app->escape($page_title) ?></title>
    <style>
            /************ Reset (only what is needed for the page) ************/
            html, body, h1, h2, div, table, thead, tbody, tr, th, td { margin: 0; padding: 0; }

            /* IE 10+ Responsive, Rather than [meta name="viewport"] IE uses this in CSS */
            @-ms-viewport { width: device-width; }

            /************ Colors ************/
            /*
                Colors for this site are based on theme colors from http://php.net/
                light-blue: #E2E4EF;
                medium-blue: #8892BF;
                dark-blue: #4F5B93;
            */

            /************ Page Contents ************/
            /*
                NOTE - CSS Class Names are based on Twitter Bootstrap Classes however Bootstrap
                is not used here. If you would like to use this template with Bootstrap rather
                than the FastSitePHP theme all you need to do is include bootstrap and remove
                the css from this section.

                CSS is hardcoded in the file rather than linking to a Bootstrap CDN so that it
                works and displays correctly offline. Additionaly Bootstrap is not included in
                the basic download of FastSitePHP so this page is designed to have no dependencies.
            */
            body {
                background-color: #8892BF;
                text-align: center;
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
            }
            .container { width:1200px; margin:50px auto; }
            @media screen and (max-width: 1300px) { .container { width:90%; } }
            .alert { margin-bottom: 40px; padding:20px; border-radius:8px; }
            .alert-danger { background-color:#4F5B93; color:#fff; }
            .alert-info { background-color:#E2E4EF; color:#4F5B93; }
            .alert, table { box-shadow:0 2px 3px 0 rgba(0,0,0,.8); }
            h1 { font-size:2em; }
            h2 { color:#4F5B93; margin-bottom:10px; font-size:1.75em; }
            table.table { text-align:left; width:100%; margin-bottom:40px; border-collapse:collapse; }
            table.table thead { background-color:#4F5B93; color:#fff; }
            .table.table > thead > tr > th { padding:12px 10px; border:1px solid #4F5B93; }
            .table.table > tbody > tr > td { padding:8px 10px; border:1px solid #4F5B93; border-bottom:none; border-right:none; vertical-align:top; }
            .table-striped>tbody>tr:nth-child(odd) { background-color:#E2E4EF; }
            .table-striped>tbody>tr:nth-child(even) { background-color:#fff; }
    </style>
  </head>
  <body>
    <div class="container">
        <div class="page-header">
            <h1 class="alert alert-danger"><?php echo $app->escape($page_title) ?></h1>
            <div class="alert alert-info"><?php echo $app->escape($message) ?></div>
        </div>
        <?php
            // Check if there is an Exception to show and if [show_detailed_errors = true]
            $has_error = ($e !== null);
            $show_detailed_errors = $has_error & $app->show_detailed_errors;

            // If [$app->show_detailed_errors = false] but the request is coming from
            // localhost '127.0.0.1' (IPv4) or '::1' (IPv6) and if the web server software
            // is also running on localhost then the error can be displayed.
            //
            // This logic is based on [FastSitePHP\Web\Request->isLocal()].
            // See comments in the source function for full details.
            //
            if ($has_error && !$show_detailed_errors) {
                // Get Client IP
                $client_ip = (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null);

                // Get Server IP
                $server_ip = null;
                if (isset($_SERVER['SERVER_ADDR'])) {
                    $server_ip = $_SERVER['SERVER_ADDR'];
                } elseif (isset($_SERVER['LOCAL_ADDR'])) {
                    $server_ip = $_SERVER['LOCAL_ADDR'];
                } elseif (php_sapi_name() === 'cli-server' && isset($_SERVER['REMOTE_ADDR'])) {
                    $server_ip = $_SERVER['REMOTE_ADDR'];
                }

                // Normalize IP's if needed
                $client_ip = ($client_ip === '[::1]' ? '::1' : $client_ip);
                $server_ip = ($server_ip === '[::1]' ? '::1' : $server_ip);

                // Check IP's
                $show_detailed_errors = (
                    ($client_ip === '127.0.0.1' || $client_ip === '::1')
                    && ($server_ip === '127.0.0.1' || $server_ip === '::1')
                );
            }
        ?>
        <?php if ($show_detailed_errors) { ?>
            <h2>Error</h2>
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-condensed text-left">
                    <thead>
                        <tr><th colspan="2">Error Source</th></tr>
                    </thead>
                    <tbody>
                        <tr><td><b>Type</b></td><td class="error-type"><?php echo $app->escape(get_class($e)) ?></td></tr>
                        <tr><td><b>Code</b></td><td class="error-code"><?php echo $app->escape($e->getCode()) ?></td></tr>
                        <?php if (get_class($e) === 'ErrorException') { ?>
                           <tr><td><b>Severity</b></td><td class="error-severity"><?php echo $app->escape($e->getSeverity()) . (isset($e->severityText) ? ' (' . $e->severityText . ')' : '') ?></td></tr>
                        <?php } ?>
                        <tr><td><b>Message</b></td><td class="error-message"><?php echo str_replace("\n", '<br>', $app->escape($e->getMessage())) ?></td></tr>
                        <tr><td><b>File</b></td><td class="error-file"><?php echo $app->escape($e->getFile()) ?></td></tr>
                        <tr><td><b>Line</b></td><td class="error-line"><?php echo $app->escape($e->getLine()) ?></td></tr>
                        <tr><td><b>Time</b></td><td class="error-time"><?php echo $app->escape(date(DATE_RFC2822)) ?></td></tr>
                    </tbody>
                </table>
            </div>

            <h2>Stack Trace</h2>
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-condensed trace-table text-left">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Function</th>
                            <th>File</th>
                            <th>Line</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $index = 0;
                        foreach ($e->getTrace() as $trace) {
                            $index++; ?>
                        <tr>
                            <td><?php echo $index ?></td>
                            <td><?php echo $app->escape($trace['function']) ?></td>
                            <td><?php echo $app->escape(isset($trace['file']) ? $trace['file'] : '') ?></td>
                            <td><?php echo $app->escape(isset($trace['line']) ? $trace['line'] : 0) ?></td>
                        </tr>
                         <?php
                        } ?>
                    </tbody>
                </table>
            </div>
        <?php } else { ?>
            <div class="alert alert-info"><strong>Time: </strong><span class="error-time"><?php echo $app->escape(date(DATE_RFC2822)) ?></span></div>
        <?php } ?>
        <script>
            // The time that comes from PHP will likely be UTC time with a format such as "Mon, 23 Apr 2018 21:19:08 +0000"
            // Convert it to a local time (example "4/23/2018, 2:19:08 PM" for US PST)
            document.addEventListener('DOMContentLoaded', function () {
                var errorTime = document.querySelector('.error-time');
                if (errorTime !== null) {
                    var d = new Date(errorTime.textContent);
                    if (!isNaN(d.getTime())) {
                        var time = (typeof d.toLocaleString === 'function' ? d.toLocaleString() : d.toString());
                        errorTime.textContent = time;
                    }
                }
            });
        </script>
    </div>
  </body>
</html>
