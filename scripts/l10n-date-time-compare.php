<!DOCTYPE html>

<?php
    // This script is used to compare the results of [Lang\L10N->format*()] with the 
    // Browser value using [Intl.DateTimeFormat]. In most cases if there are differences 
    // the browser is assumed to be correct, however it not always is. For example
    // on a tested computer in the US, language [az] is coming back with the incorrect
    // format while the [L10N] format is correct for Azerbaijan [az]. In most cases the  
    // differences happen to be two-digit padded months/days vs 1-digit days months/days.
    // 
    // Each browser returns these values with variations when using [Intl.DateTimeFormat]
    // so they are unlikely to match what is generated from PHP as well for many languages.
    // If comparing with IE all values will look different, this mainly tested with
    // Chrome and Safari.
    //
    // Also helpfull for development and comparing:
    //   http://demo.icu-project.org/icu-bin/locexp
    //   http://demo.icu-project.org/icu-bin/locexp?d_=en&_=en_US

    // Autoloader and Setup App
    require __DIR__ . '/../autoload.php';
    $app = new \FastSitePHP\Application();
    $app->setup('UTC');
    $app->show_detailed_errors = true;
    set_time_limit(0);

    $l10n = new \FastSitePHP\Lang\L10N();
    $all_langs = $l10n->supportedLocales();

    $date = time();

    // Change this based on your local settings
    $l10n->timezone('America/Los_Angeles');
?>

<html lang="en">
    <head>
        <meta charset="utf-8" />
        <title></title>
        <style>
            body { margin:100px; }
            table { border-collapse:collapse; display:inline-block; margin:20px; }
            table caption { font-size:1.5em; font-weight:bold; line-height:1.5em; margin-bottom:.5em; }
            td, th { border:1px solid #000; padding:5px 10px; white-space:nowrap; }
        </style>
    </head>
    <body>
        <div style="font-size:1.5em; margin-left:50px;"><strong>Browser Language:</strong>  <span id="language"></span></div>
        <div style="font-size:1.5em; margin-left:50px;"><strong>Browser Time:</strong>  <span id="now"></span></div>

        <table id="date-time-table">
            <caption>Date/Time Formatting</caption>
            <thead>
                <tr>
                    <th rowspan="2">Language</th>
                    <th colspan="2">Date/Time</th>
                    <th colspan="2">Date</th>
                    <th colspan="2">Time</th>
                </tr>
                <tr>
                    <th>PHP</th>
                    <th>JS</th>
                    <th>PHP</th>
                    <th>JS</th>
                    <th>PHP</th>
                    <th>JS</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($all_langs as $lang) { ?>
                <tr>
                    <td><?php echo $lang ?></td>
                    <td><?php echo $l10n->locale($lang)->formatDateTime($date) ?></td>
                    <td></td>
                    <td><?php echo $l10n->locale($lang)->formatDate($date) ?></td>
                    <td></td>
                    <td><?php echo $l10n->locale($lang)->formatTime($date) ?></td>
                    <td></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>

        <script>
            window.dateValue = <?php echo $date ?>;
        </script>
        <script>
            function dateTime(dateTime, lang, options) {
                if (window.Intl === undefined) {
                    return dateTime;
                }

                if (Intl.DateTimeFormat.supportedLocalesOf([lang], {localMatcher:'lookup'}).length === 0) {
                    return '';
                }

                // Return formatted date/time in the user's local language
                try {
                    if (dateTime instanceof Date) {
                        return new Intl.DateTimeFormat(lang, options).format(dateTime);
                    } else {
                        var localDate = new Date(dateTime);
                        var utcDate = new Date(localDate.getUTCFullYear(), localDate.getUTCMonth(), localDate.getUTCDate(), localDate.getUTCHours(), localDate.getUTCMinutes(), localDate.getUTCSeconds(), localDate.getUTCMilliseconds());
                        return new Intl.DateTimeFormat(lang, options).format(utcDate);
                    }
                } catch (e) {
                    // If Error log to console and return "Error" text
                    console.warn("Error formatting Date/Time Value:");
                    console.log(navigator.language);
                    console.log(options);
                    console.log(dateTime);
                    console.log(e);
                    return "Error";
                }
            }

            function formatDate(date, lang) {
                return dateTime(date, lang, {});
            }

            function formatDateTime(date, lang) {
                var intlOptions = { year: 'numeric', month: 'numeric', day: 'numeric', hour: 'numeric', minute: 'numeric' };
                return dateTime(date, lang, intlOptions);
            }

            function formatTime(date, lang) {
                var intlOptions = { hour: 'numeric', minute: 'numeric', second: 'numeric' };
                return dateTime(date, lang, intlOptions);
            }

            const dateValue = new Date(window.dateValue * 1000);
            const table = document.getElementById("date-time-table");
            const rows = table.tBodies[0].rows;
            for (let n = 0, m = rows.length; n < m; n++) {
                const lang = rows[n].cells[0].textContent;
                rows[n].cells[2].textContent = formatDateTime(dateValue, lang);
                if (rows[n].cells[2].textContent !== rows[n].cells[1].textContent) {
                    rows[n].cells[2].style.backgroundColor = '#ffc8c8';
                }
                rows[n].cells[4].textContent = formatDate(dateValue, lang);
                if (rows[n].cells[4].textContent !== rows[n].cells[3].textContent) {
                    rows[n].cells[4].style.backgroundColor = '#ffc8c8';
                }
                rows[n].cells[6].textContent = formatTime(dateValue, lang);
                if (rows[n].cells[6].textContent !== rows[n].cells[5].textContent) {
                    rows[n].cells[6].style.backgroundColor = '#ffc8c8';
                }
            }

            document.getElementById("language").innerHTML = navigator.language;
            document.getElementById("now").innerHTML = new Date();
        </script>
    </body>
</html>
